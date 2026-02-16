(function () {
  'use strict';

  /* ══════════════ HELPERS ══════════════ */
  function $(sel, ctx) { return (ctx || document).querySelector(sel); }
  function $$(sel, ctx) { return Array.from((ctx || document).querySelectorAll(sel)); }

  var apiBase = (function () {
    var s = document.querySelector('script[src*="app.js"]');
    if (s) {
      var src = s.getAttribute('src');
      return src.replace('assets/js/app.js', 'api.php');
    }
    return 'api.php';
  })();

  function post(action, data) {
    var fd = new FormData();
    fd.append('action', action);
    for (var k in data) fd.append(k, data[k]);
    return fetch(apiBase, { method: 'POST', body: fd }).then(function (r) { return r.json(); });
  }

  function getJSON(action) {
    return fetch(apiBase + '?action=' + action).then(function (r) { return r.json(); });
  }

  function toast(msg, ms) {
    var el = document.createElement('div');
    el.className = 'toast';
    el.textContent = msg;
    document.body.appendChild(el);
    setTimeout(function () {
      el.classList.add('hide');
      setTimeout(function () { el.remove(); }, 250);
    }, ms || 2500);
  }

  /* ══════════════ REAL-TIME SYNC ══════════════ */
  var currentHash = null;
  var pollTimer = null;
  var POLL_INTERVAL = 3000;
  var channel = null;
  var isReloading = false;

  function initSync() {
    // 1) BroadcastChannel — aynı tarayıcıdaki sekmeler arası anlık sync
    if (typeof BroadcastChannel !== 'undefined') {
      channel = new BroadcastChannel('noteflow_sync');
      channel.onmessage = function (e) {
        if (e.data && e.data.type === 'data_changed' && !isReloading) {
          isReloading = true;
          location.reload();
        }
      };
    }

    // 2) İlk hash'i al
    getJSON('check_version').then(function (res) {
      if (res.hash) currentHash = res.hash;
    }).catch(function () {});

    // 3) Polling — farklı tarayıcı/cihaz arası sync
    pollTimer = setInterval(function () {
      if (isReloading) return;
      if (document.hidden) return; // sekme arka plandaysa atla
      getJSON('check_version').then(function (res) {
        if (!res.hash) return;
        if (currentHash && res.hash !== currentHash && !isReloading) {
          isReloading = true;
          location.reload();
        }
        currentHash = res.hash;
      }).catch(function () {});
    }, POLL_INTERVAL);

    // Sekme aktif olduğunda hemen kontrol et
    document.addEventListener('visibilitychange', function () {
      if (!document.hidden && !isReloading) {
        getJSON('check_version').then(function (res) {
          if (!res.hash) return;
          if (currentHash && res.hash !== currentHash && !isReloading) {
            isReloading = true;
            location.reload();
          }
          currentHash = res.hash;
        }).catch(function () {});
      }
    });
  }

  // Diğer sekmelere veri değişikliğini bildir
  function notifyChange() {
    if (channel) {
      channel.postMessage({ type: 'data_changed', time: Date.now() });
    }
    // Kendi hash'imizi güncelle (reload yapmadan önce)
    getJSON('check_version').then(function (res) {
      if (res.hash) currentHash = res.hash;
    }).catch(function () {});
  }

  /* ══════════════ DROPDOWNS ══════════════ */
  document.addEventListener('click', function (e) {
    var nav = document.getElementById('user-nav');
    if (nav && !nav.contains(e.target)) nav.classList.remove('open');
    $$('.dropdown-inline').forEach(function (d) {
      if (!d.contains(e.target)) d.classList.remove('open');
    });
  });

  /* ══════════════ DRAG & DROP ══════════════ */
  var dragNoteId = null;

  function initDragDrop() {
    $$('.note-card, .note-row').forEach(function (el) {
      el.setAttribute('draggable', 'true');
      el.addEventListener('dragstart', function (e) {
        var id = el.dataset.noteId;
        if (!id) return;
        dragNoteId = id;
        el.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', id);
      });
      el.addEventListener('dragend', function () {
        el.classList.remove('dragging');
        dragNoteId = null;
        $$('.folder-row').forEach(function (f) { f.classList.remove('drop-over'); });
      });
    });

    $$('.folder-row[data-folder-id]').forEach(function (fRow) {
      fRow.addEventListener('dragover', function (e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        fRow.classList.add('drop-over');
      });
      fRow.addEventListener('dragleave', function () {
        fRow.classList.remove('drop-over');
      });
      fRow.addEventListener('drop', function (e) {
        e.preventDefault();
        fRow.classList.remove('drop-over');
        var noteId = e.dataTransfer.getData('text/plain') || dragNoteId;
        var folderId = fRow.dataset.folderId;
        if (!noteId || !folderId) return;
        post('move_note', { note_id: noteId, folder_id: folderId }).then(function (res) {
          if (res.ok) {
            toast('"' + fRow.querySelector('.folder-link span').textContent + '" klasörüne taşındı');
            notifyChange();
            setTimeout(function () { location.reload(); }, 600);
          }
        });
      });
    });
  }

  /* ══════════════ AJAX FAVORITE ══════════════ */
  function initFavorite() {
    $$('[data-fav-btn]').forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var noteId = btn.dataset.noteId;
        if (!noteId) return;
        post('toggle_favorite', { note_id: noteId }).then(function (res) {
          if (res.ok) {
            var card = btn.closest('.note-card, .note-row');
            if (card) card.classList.toggle('favorite');
            toast(res.isFavorite ? 'Favorilere eklendi' : 'Favorilerden çıkarıldı');
            notifyChange();
          }
        });
      });
    });
  }

  /* ══════════════ AJAX DELETE ══════════════ */
  function initDelete() {
    $$('[data-delete-btn]').forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        if (!confirm('Bu notu silmek istediğinize emin misiniz?')) return;
        var noteId = btn.dataset.noteId;
        if (!noteId) return;
        post('delete_note', { note_id: noteId }).then(function (res) {
          if (res.ok) {
            var card = btn.closest('.note-card, .note-row');
            if (card) {
              card.style.transition = 'opacity .2s, transform .2s';
              card.style.opacity = '0';
              card.style.transform = 'scale(.9)';
              setTimeout(function () { card.remove(); }, 250);
            }
            toast('Not silindi');
            notifyChange();
          }
        });
      });
    });
  }

  /* ══════════════ NOTE MODAL EDITOR ══════════════ */
  function initNoteModal() {
    var overlay = document.getElementById('note-modal');
    if (!overlay) return;

    var form       = $('#note-modal-form');
    var titleInput = $('[name="title"]', form);
    var bodyInput  = $('[name="content"]', form);
    var colorInput = $('[name="color"]', form);
    var idInput    = $('[name="note_id"]', form);
    var folderSel  = $('[name="folder_id"]', form);
    var favCheck   = $('[name="is_favorite"]', form);
    var modalBg    = $('.note-editor-body', overlay);

    function openEditor(data) {
      data = data || {};
      idInput.value    = data.id || '';
      titleInput.value = data.title || '';
      bodyInput.value  = data.content || '';
      colorInput.value = data.color || '#FFDAB9';
      if (folderSel) folderSel.value = data.folderId || 'none';
      if (favCheck) favCheck.checked = !!data.isFavorite;
      setEditorColor(data.color || '#FFDAB9');
      // Favori ikon durumunu güncelle
      var favBtn = $('[data-modal-fav]', overlay);
      if (favBtn) {
        var svg = favBtn.querySelector('svg');
        if (svg) svg.style.fill = data.isFavorite ? 'currentColor' : 'none';
      }
      overlay.classList.add('open');
      titleInput.focus();
    }

    function closeEditor() {
      overlay.classList.remove('open');
    }

    function setEditorColor(c) {
      if (modalBg) modalBg.style.backgroundColor = c;
      $$('.color-dot', overlay).forEach(function (d) {
        d.classList.toggle('selected', d.dataset.color === c);
      });
      colorInput.value = c;
    }

    $$('.color-dot', overlay).forEach(function (d) {
      d.addEventListener('click', function () { setEditorColor(d.dataset.color); });
    });

    var closeBtn = $('[data-close-modal]', overlay);
    if (closeBtn) closeBtn.addEventListener('click', closeEditor);
    overlay.addEventListener('click', function (e) { if (e.target === overlay) closeEditor(); });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && overlay.classList.contains('open')) closeEditor();
    });

    // Save
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var title = titleInput.value.trim();
      if (!title) { titleInput.focus(); return; }
      post('save_note', {
        note_id:     idInput.value,
        title:       title,
        content:     bodyInput.value,
        color:       colorInput.value,
        folder_id:   folderSel ? folderSel.value : 'none',
        is_favorite: favCheck && favCheck.checked ? '1' : ''
      }).then(function (res) {
        if (res.ok) {
          closeEditor();
          toast(idInput.value ? 'Not güncellendi' : 'Not oluşturuldu');
          notifyChange();
          setTimeout(function () { location.reload(); }, 500);
        } else {
          toast(res.error || 'Hata oluştu');
        }
      });
    });

    // Favorite inside modal
    var modalFavBtn = $('[data-modal-fav]', overlay);
    if (modalFavBtn) {
      modalFavBtn.addEventListener('click', function () {
        favCheck.checked = !favCheck.checked;
        var svg = modalFavBtn.querySelector('svg');
        if (svg) svg.style.fill = favCheck.checked ? 'currentColor' : 'none';
      });
    }

    // Delete inside modal
    var modalDelBtn = $('[data-modal-delete]', overlay);
    if (modalDelBtn) {
      modalDelBtn.addEventListener('click', function () {
        var nid = idInput.value;
        if (!nid) { closeEditor(); return; }
        if (!confirm('Bu notu silmek istediğinize emin misiniz?')) return;
        post('delete_note', { note_id: nid }).then(function (res) {
          if (res.ok) {
            closeEditor();
            toast('Not silindi');
            notifyChange();
            setTimeout(function () { location.reload(); }, 500);
          }
        });
      });
    }

    // "Yeni Not" button
    $$('[data-new-note]').forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        openEditor();
      });
    });

    // Click on note card to edit
    $$('.note-card .note-body, .note-row .note-row-content').forEach(function (el) {
      el.addEventListener('click', function (e) {
        e.preventDefault();
        var card = el.closest('[data-note-id]');
        if (!card) return;
        openEditor({
          id:         card.dataset.noteId,
          title:      card.dataset.noteTitle || '',
          content:    card.dataset.noteContent || '',
          color:      card.dataset.noteColor || '#FFDAB9',
          folderId:   card.dataset.noteFolderId || '',
          isFavorite: card.dataset.noteFavorite === '1'
        });
      });
    });
  }

  /* ══════════════ MOBILE SIDEBAR ══════════════ */
  function initMobileSidebar() {
    // ESC tuşu ile sidebar'ı kapat
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && document.body.classList.contains('sidebar-open')) {
        document.body.classList.remove('sidebar-open');
      }
    });

    // Sidebar içindeki linklere tıklayınca mobilde sidebar'ı kapat
    $$('.sidebar a').forEach(function (link) {
      link.addEventListener('click', function () {
        if (window.innerWidth <= 640) {
          document.body.classList.remove('sidebar-open');
        }
      });
    });

    // Pencere boyutu değişince sidebar-open'ı kaldır
    window.addEventListener('resize', function () {
      if (window.innerWidth > 640) {
        document.body.classList.remove('sidebar-open');
      }
    });
  }

  /* ══════════════ INIT ══════════════ */
  document.addEventListener('DOMContentLoaded', function () {
    initDragDrop();
    initFavorite();
    initDelete();
    initNoteModal();
    initSync();
    initMobileSidebar();
  });
})();
