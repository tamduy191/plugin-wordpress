/* ToanWeb Product Reviews — Frontend JS */
(function () {
  'use strict';

  const cfg = window.TWR || {};
  const $ = (sel, ctx = document) => ctx.querySelector(sel);
  const $$ = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));

  /* ── Apply primary color from settings ── */
  document.documentElement.style.setProperty('--twr-primary',   cfg.primary   || '#64b2fa');
  document.documentElement.style.setProperty('--twr-bar-fill',  cfg.bar_fill  || '#64b2fa');
  document.documentElement.style.setProperty('--twr-bar-track', cfg.bar_track || '#e5e5ea');

  /* ── Portal: move modal/toast/lightbox ra document.body ── */
  ['#twr-form-overlay', '#twr-success-toast', '#twr-lightbox', '#twr-photo-gallery'].forEach(id => {
    const el = document.querySelector(id);
    if (el) document.body.appendChild(el);
  });

  /* ── Star picker ── */
  const starLabels = ['', 'Rất tệ', 'Tệ', 'Bình thường', 'Tốt', 'Tuyệt vời'];

  function initStarPicker(picker) {
    const stars = $$('.twr-star-pick', picker);
    const input = document.querySelector(`input[name="${picker.dataset.name}"]`);
    const label = $('.twr-star-pick-label', picker);

    stars.forEach((star, idx) => {
      star.addEventListener('mouseenter', () => highlight(idx));
      star.addEventListener('mouseleave', () => highlightSelected());
      star.addEventListener('click', () => {
        picker.dataset.selected = idx + 1;
        if (input) input.value = idx + 1;
        if (label) label.textContent = starLabels[idx + 1] || '';
        highlightSelected();
      });
    });

    function highlight(upTo) {
      stars.forEach((s, i) => s.classList.toggle('twr-selected', i <= upTo));
    }

    function highlightSelected() {
      const sel = parseInt(picker.dataset.selected || 0);
      stars.forEach((s, i) => s.classList.toggle('twr-selected', i < sel));
    }
  }

  $$('.twr-star-picker').forEach(initStarPicker);

  /* ── Modal open/close ── */
  const overlay = $('#twr-form-overlay');
  const openBtn = $('#twr-open-form');
  const closeBtn = $('#twr-close-form');

  function openModal() {
    if (!overlay) return;
    clearErrors();
    overlay.classList.add('twr-open');
    overlay.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  }

  function closeModal() {
    if (!overlay) return;
    overlay.classList.remove('twr-open');
    overlay.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  }

  openBtn && openBtn.addEventListener('click', openModal);
  closeBtn && closeBtn.addEventListener('click', closeModal);
  overlay && overlay.addEventListener('click', (e) => {
    if (e.target === overlay) closeModal();
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeModal();
  });

  /* ── Skeleton loader ── */
  function skeletonHTML(count = 3) {
    const item = `
      <div class="twr-skeleton">
        <div class="twr-skeleton__header">
          <div class="twr-skel twr-skel--avatar"></div>
          <div class="twr-skeleton__meta">
            <div class="twr-skel twr-skel--name"></div>
            <div class="twr-skel twr-skel--stars"></div>
          </div>
        </div>
        <div class="twr-skel twr-skel--line"></div>
        <div class="twr-skel twr-skel--line twr-skel--line-short"></div>
        <div class="twr-skeleton__photos">
          <div class="twr-skel twr-skel--photo"></div>
          <div class="twr-skel twr-skel--photo"></div>
          <div class="twr-skel twr-skel--photo"></div>
        </div>
      </div>`;
    return Array(count).fill(item).join('');
  }

  /* ── Filter bar ── */
  const filterBtns = $$('.twr-filter-btn');
  const list = $('#twr-review-list');
  const loadMoreBtn = $('#twr-load-more');
  const paginationEl = $('#twr-pagination');
  let currentFilter = 'all';
  let currentPage = 1;
  let isLoading = false;

  filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      filterBtns.forEach(b => b.classList.remove('twr-filter-btn--active'));
      btn.classList.add('twr-filter-btn--active');
      currentFilter = btn.dataset.filter;
      currentPage = 1;

      if (list) list.innerHTML = skeletonHTML();
      if (loadMoreBtn) loadMoreBtn.dataset.offset = 0;

      fetchReviews(0, currentFilter, true);
    });
  });

  /* ── Load more ── */
  loadMoreBtn && loadMoreBtn.addEventListener('click', () => {
    const offset = parseInt(loadMoreBtn.dataset.offset || 0);
    fetchReviews(offset, currentFilter, false);
  });

  /* ── Pagination ── */
  function getPageRange(current, total) {
    const delta = 1;
    const range = [];
    for (let i = 1; i <= total; i++) {
      if (i === 1 || i === total || (i >= current - delta && i <= current + delta)) {
        range.push(i);
      }
    }
    const result = [];
    let prev;
    for (const i of range) {
      if (prev !== undefined) {
        if (i - prev === 2) result.push(prev + 1);
        else if (i - prev > 2) result.push('...');
      }
      result.push(i);
      prev = i;
    }
    return result;
  }

  function renderPagination(total, page) {
    if (!paginationEl) return;
    const perPage = parseInt(paginationEl.dataset.perPage, 10) || parseInt(cfg.per_page, 10) || 5;
    const totalPages = Math.ceil(total / perPage);

    if (totalPages <= 1) { paginationEl.innerHTML = ''; return; }

    paginationEl.innerHTML = getPageRange(page, totalPages).map(p =>
      p === '...'
        ? `<span class="twr-page-ellipsis">…</span>`
        : `<button class="twr-page-btn${p === page ? ' twr-page-btn--active' : ''}" data-page="${p}">${p}</button>`
    ).join('');

    $$('.twr-page-btn', paginationEl).forEach(btn => {
      btn.addEventListener('click', () => {
        const p = parseInt(btn.dataset.page, 10);
        if (p === currentPage || isLoading) return;
        currentPage = p;
        const offset = (p - 1) * perPage;
        if (list) list.innerHTML = skeletonHTML();
        fetchReviews(offset, currentFilter, true);
        // Scroll to top of list
        list && list.scrollIntoView({ behavior: 'smooth', block: 'start' });
      });
    });
  }

  // Initialize pagination on page load
  if (paginationEl) {
    renderPagination(parseInt(paginationEl.dataset.total, 10) || 0, 1);
  }

  function fetchReviews(offset, filter, replace) {
    if (isLoading) return;
    isLoading = true;
    if (loadMoreBtn) loadMoreBtn.disabled = true;

    const body = new FormData();
    body.append('action', 'twr_load_reviews');
    body.append('product_id', cfg.product_id);
    body.append('offset', offset);
    body.append('filter', filter);

    fetch(cfg.ajax_url, { method: 'POST', body })
      .then(r => r.json())
      .then(res => {
        if (!res.success) return;
        const { html, total } = res.data;

        if (replace) {
          list.innerHTML = html || `<p class="twr-empty">${'Không có đánh giá nào.'}</p>`;
        } else {
          list.insertAdjacentHTML('beforeend', html);
        }

        if (paginationEl) {
          renderPagination(total, currentPage);
        } else if (loadMoreBtn) {
          const newOffset = offset + parseInt(cfg.per_page, 10);
          loadMoreBtn.dataset.offset = newOffset;
          if (newOffset >= total) {
            loadMoreBtn.disabled = true;
            loadMoreBtn.textContent = cfg.i18n.no_more;
          } else {
            loadMoreBtn.disabled = false;
          }
        }

        initPhotoThumbs();
        initHelpfulBtns();
        applyMasonryWhenReady(list);
      })
      .catch(console.error)
      .finally(() => { isLoading = false; });
  }

  /* ── Lightbox ── */
  const lb        = $('#twr-lightbox');
  const lbImgWrap = $('#twr-lb-img-wrap');
  const lbReview  = $('#twr-lb-review');
  const lbCounter = $('#twr-lb-counter');
  let lbGallery        = [];
  let lbReviewData     = {};
  let lbReviewGallery  = [];   // per-photo review data when opened from photo gallery
  let lbIndex          = 0;

  const starLabelsLb = ['', 'Rất tệ', 'Tệ', 'Bình thường', 'Tốt', 'Tuyệt vời'];

  function openLightbox(gallery, index, review, force, reviewGallery) {
    if (!lb || (!cfg.lightbox && !force)) return;
    lbGallery       = gallery;
    lbIndex         = index;
    lbReviewGallery = reviewGallery || [];
    lbReviewData    = lbReviewGallery[index] || review || {};
    renderLightbox();
    lb.classList.add('twr-lb-open');
    lb.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  }

  function closeLightbox() {
    if (!lb) return;
    lb.classList.remove('twr-lb-open');
    lb.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  }

  function renderLightbox() {
    // Image panel
    if (lbImgWrap) {
      const url = lbGallery[lbIndex];
      lbImgWrap.innerHTML = `<img src="${url}" alt="" draggable="false">`;
    }
    if (lbCounter) {
      lbCounter.textContent = lbGallery.length > 1 ? `${lbIndex + 1} / ${lbGallery.length}` : '';
    }
    const prevBtn = $('#twr-lb-prev');
    const nextBtn = $('#twr-lb-next');
    if (prevBtn) prevBtn.style.display = lbGallery.length > 1 ? '' : 'none';
    if (nextBtn) nextBtn.style.display = lbGallery.length > 1 ? '' : 'none';

    // Review panel
    if (!lbReview) return;
    const r = lbReviewData;
    if (!r.author) { lbReview.innerHTML = ''; return; }

    const starSvg = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';
    const stars = Array.from({ length: 5 }, (_, i) =>
      `<span class="twr-star${i < r.rating ? ' twr-star--filled' : ''}">${starSvg}</span>`
    ).join('');

    const verifiedHTML = r.verified && r.verified_text
      ? `<span class="twr-badge-verified">
           <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
           ${escHtml(r.verified_text)}
         </span>`
      : '';

    const contentHTML = r.content
      ? `<p class="twr-lb-review__content">${escHtml(r.content)}</p>`
      : '';

    const dateHTML = r.date
      ? `<p class="twr-lb-review__date">
           <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
           Đánh giá đã đăng vào ${escHtml(r.date)}
         </p>`
      : '';

    lbReview.innerHTML = `
      <div class="twr-lb-review__header">
        <img class="twr-lb-review__avatar" src="${escHtml(r.avatar)}" alt="${escHtml(r.author)}" width="48" height="48">
        <div class="twr-lb-review__info">
          <p class="twr-lb-review__author">${escHtml(r.author)}</p>
          ${verifiedHTML}
          <div class="twr-stars twr-stars--sm">${stars}
            <span class="twr-rating-label">${escHtml(starLabelsLb[r.rating] || '')}</span>
          </div>
        </div>
      </div>
      ${contentHTML}
      ${dateHTML}
    `;
  }

  function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  lb && $('#twr-lb-backdrop') && $('#twr-lb-backdrop').addEventListener('click', closeLightbox);
  lb && $('#twr-lb-close')    && $('#twr-lb-close').addEventListener('click', closeLightbox);

  function syncLbReview() {
    if (lbReviewGallery.length) lbReviewData = lbReviewGallery[lbIndex] || lbReviewData;
  }

  $('#twr-lb-prev') && $('#twr-lb-prev').addEventListener('click', () => {
    lbIndex = (lbIndex - 1 + lbGallery.length) % lbGallery.length;
    syncLbReview();
    renderLightbox();
  });

  $('#twr-lb-next') && $('#twr-lb-next').addEventListener('click', () => {
    lbIndex = (lbIndex + 1) % lbGallery.length;
    syncLbReview();
    renderLightbox();
  });

  document.addEventListener('keydown', (e) => {
    if (!lb || !lb.classList.contains('twr-lb-open')) return;
    if (e.key === 'ArrowLeft')  { lbIndex = (lbIndex - 1 + lbGallery.length) % lbGallery.length; syncLbReview(); renderLightbox(); }
    if (e.key === 'ArrowRight') { lbIndex = (lbIndex + 1) % lbGallery.length; syncLbReview(); renderLightbox(); }
    if (e.key === 'Escape')     closeLightbox();
  });

  function initPhotoThumbs() {
    $$('.twr-photo-thumb').forEach(btn => {
      if (btn._twrBound) return;
      btn._twrBound = true;
      btn.addEventListener('click', () => {
        const gallery = JSON.parse(btn.dataset.gallery || '[]');
        const index   = parseInt(btn.dataset.index || 0, 10);
        const review  = JSON.parse(btn.dataset.review || '{}');
        openLightbox(gallery, index, review);
      });
    });
  }

  initPhotoThumbs();

  /* ── Helpful votes ── */
  const votedKey = 'twr_voted';
  const voted = JSON.parse(localStorage.getItem(votedKey) || '[]');

  function initHelpfulBtns() {
    $$('.twr-helpful-btn').forEach(btn => {
      if (btn._twrBound) return;
      btn._twrBound = true;

      const id = btn.dataset.id;
      if (voted.includes(id)) btn.classList.add('twr-helpful-btn--voted');

      btn.addEventListener('click', () => {
        if (voted.includes(id)) return;

        const body = new FormData();
        body.append('action', 'twr_helpful');
        body.append('comment_id', id);

        fetch(cfg.ajax_url, { method: 'POST', body })
          .then(r => r.json())
          .then(res => {
            if (!res.success) return;
            const countEl = btn.querySelector('.twr-helpful-count');
            if (countEl) countEl.textContent = `(${res.data.count})`;
            btn.classList.add('twr-helpful-btn--voted');
            voted.push(id);
            localStorage.setItem(votedKey, JSON.stringify(voted));
          });
      });
    });
  }

  initHelpfulBtns();

  /* ── Form submit (AJAX) ── */
  const form       = $('#twr-review-form');
  const submitBtn  = $('#twr-submit-btn');
  const formError  = $('#twr-form-error');
  const toast      = $('#twr-success-toast');
  const toastMsg   = $('#twr-toast-msg');

  function fieldError(fieldId, msg) {
    const el = $(`#twr-error-${fieldId}`);
    const wrap = $(`#twr-field-${fieldId}`);
    if (el) {
      el.textContent = msg;
      el.classList.toggle('twr-field-error--show', !!msg);
    }
    if (wrap) wrap.classList.toggle('twr-field--error', !!msg);
  }

  function clearErrors() {
    $$('.twr-field-error').forEach(el => {
      el.textContent = '';
      el.classList.remove('twr-field-error--show');
    });
    $$('.twr-field--error').forEach(el => el.classList.remove('twr-field--error'));
    if (formError) formError.textContent = '';
  }

  /* ── Character counter ── */
  const commentArea = $('#twr-comment');
  const maxCommentLen = parseInt(cfg.max_comment_length, 10) || 0;

  if (commentArea && maxCommentLen > 0) {
    commentArea.setAttribute('maxlength', maxCommentLen);
    const counter = document.createElement('div');
    counter.className = 'twr-char-counter';
    counter.setAttribute('aria-live', 'polite');
    counter.textContent = `0 / ${maxCommentLen} ký tự`;
    commentArea.insertAdjacentElement('afterend', counter);

    commentArea.addEventListener('input', () => {
      const len = commentArea.value.length;
      counter.textContent = `${len} / ${maxCommentLen} ký tự`;
      counter.classList.toggle('twr-char-counter--warn', len >= maxCommentLen * 0.9);
      counter.classList.toggle('twr-char-counter--full', len >= maxCommentLen);
    });
  }

  function validate() {
    let ok = true;
    const rating = $('#twr-rating-input');
    if (!rating || !rating.value) {
      fieldError('rating', 'Vui lòng chọn số sao đánh giá.');
      ok = false;
    }
    const author = $('#twr-author');
    if (author && !author.value.trim()) {
      fieldError('author', 'Vui lòng nhập tên của bạn.');
      ok = false;
    }
    const email = $('#twr-email');
    if (email) {
      const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!re.test(email.value.trim())) {
        fieldError('email', 'Email không hợp lệ.');
        ok = false;
      }
    }
    if (commentArea && maxCommentLen > 0 && commentArea.value.length > maxCommentLen) {
      if (formError) formError.textContent = `Nội dung tối đa ${maxCommentLen} ký tự.`;
      ok = false;
    }
    return ok;
  }

  function showToast(msg) {
    if (!toast) return;
    if (toastMsg) toastMsg.textContent = msg;
    toast.classList.add('twr-toast--show');
    toast.setAttribute('aria-hidden', 'false');
    setTimeout(() => {
      toast.classList.remove('twr-toast--show');
      toast.setAttribute('aria-hidden', 'true');
    }, 4000);
  }

  form && form.addEventListener('submit', (e) => {
    e.preventDefault();
    clearErrors();
    if (!validate()) return;

    const fd = new FormData(form);

    submitBtn.disabled = true;
    submitBtn.textContent = 'Đang gửi...';

    fetch(cfg.ajax_url, { method: 'POST', body: fd })
      .then(r => r.json())
      .then(res => {
        if (res.success) {
          closeModal();
          showToast(res.data.message || 'Cảm ơn bạn đã đánh giá!');
          form.reset();
          selectedFiles = [];
          if (uploadPreview) uploadPreview.innerHTML = '';
          if (uploadPlaceholder) uploadPlaceholder.style.display = '';
          $$('.twr-star-picker').forEach(p => {
            delete p.dataset.selected;
            $$('.twr-star-pick', p).forEach(s => s.classList.remove('twr-selected'));
            const lbl = $('.twr-star-pick-label', p);
            if (lbl) lbl.textContent = '';
          });
          // Reload review list from offset 0
          if (list) {
            list.innerHTML = skeletonHTML();
            fetchReviews(0, currentFilter, true);
          }
        } else {
          const err = res.data || {};
          if (err.field) {
            fieldError(err.field, err.message);
          } else {
            if (formError) formError.textContent = err.message || 'Có lỗi xảy ra, vui lòng thử lại.';
          }
        }
      })
      .catch(() => {
        if (formError) formError.textContent = 'Không thể kết nối server.';
      })
      .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Gửi đánh giá';
      });
  });

  /* ── Upload preview ── */
  const mediaInput = $('#twr-media-input');
  const uploadPreview = $('#twr-upload-preview');
  const uploadPlaceholder = $('#twr-upload-placeholder');
  const uploadArea = $('#twr-upload-area');
  let selectedFiles = [];
  let syncingInput = false;

  mediaInput && mediaInput.addEventListener('change', () => {
    if (syncingInput) return;
    handleFiles(mediaInput.files);
  });

  uploadArea && uploadArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadArea.classList.add('twr-drag-over');
  });
  uploadArea && uploadArea.addEventListener('dragleave', () => {
    uploadArea.classList.remove('twr-drag-over');
  });
  uploadArea && uploadArea.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadArea.classList.remove('twr-drag-over');
    handleFiles(e.dataTransfer.files);
  });

  const maxFileCount = parseInt(cfg.max_file_count, 10) || 5;
  const maxFileSizeBytes = (parseInt(cfg.max_file_size, 10) || 5120) * 1024;
  const uploadAccept = cfg.upload_accept || 'both';

  function isFileTypeAllowed(file) {
    if (uploadAccept === 'images') return file.type.startsWith('image/');
    if (uploadAccept === 'videos') return file.type.startsWith('video/');
    return file.type.startsWith('image/') || file.type.startsWith('video/');
  }

  function handleFiles(files) {
    const arr = Array.from(files).slice(0, maxFileCount - selectedFiles.length);
    arr.forEach(file => {
      if (selectedFiles.length >= maxFileCount) return;
      if (!isFileTypeAllowed(file)) {
        const typeLabel = uploadAccept === 'images' ? 'ảnh' : uploadAccept === 'videos' ? 'video' : 'ảnh hoặc video';
        alert(`"${file.name}" không đúng định dạng. Chỉ chấp nhận ${typeLabel}.`);
        return;
      }
      if (file.size > maxFileSizeBytes) {
        const mb = (maxFileSizeBytes / 1048576).toFixed(1);
        alert(`"${file.name}" vượt quá kích thước tối đa ${mb}MB.`);
        return;
      }
      selectedFiles.push(file);
      renderPreview(file, selectedFiles.length - 1);
    });
    if (uploadPlaceholder) {
      uploadPlaceholder.style.display = selectedFiles.length ? 'none' : '';
    }
    syncFileInput();
  }

  function renderPreview(file, idx) {
    if (!uploadPreview) return;
    const wrap = document.createElement('div');
    wrap.className = 'twr-preview-remove';
    wrap.dataset.idx = idx;

    const isVideo = file.type.startsWith('video/');
    const el = document.createElement(isVideo ? 'video' : 'img');
    el.className = 'twr-preview-thumb';
    if (isVideo) { el.src = URL.createObjectURL(file); el.muted = true; }
    else { el.src = URL.createObjectURL(file); el.alt = ''; }

    const rmBtn = document.createElement('button');
    rmBtn.type = 'button';
    rmBtn.className = 'twr-preview-remove-btn';
    rmBtn.innerHTML = '×';
    rmBtn.addEventListener('click', () => {
      selectedFiles.splice(idx, 1);
      wrap.remove();
      // Re-index remaining
      $$('.twr-preview-remove', uploadPreview).forEach((w, i) => w.dataset.idx = i);
      if (uploadPlaceholder) uploadPlaceholder.style.display = selectedFiles.length ? 'none' : '';
      syncFileInput();
    });

    wrap.appendChild(el);
    wrap.appendChild(rmBtn);
    uploadPreview.appendChild(wrap);
  }

  function syncFileInput() {
    if (!mediaInput) return;
    syncingInput = true;
    const dt = new DataTransfer();
    selectedFiles.forEach(f => dt.items.add(f));
    mediaInput.files = dt.files;
    syncingInput = false;
  }

  /* ── Photo Gallery ── */
  const pgOverlay  = $('#twr-photo-gallery');
  const pgGrid     = $('#twr-gallery-grid');
  const pgTabs     = $('#twr-gallery-tabs');
  const pgTitle    = $('#twr-gallery-title');
  const pgClose    = $('#twr-gallery-close');

  let pgPhotos = [];
  let pgFilter = 'all';

  const photosScript = document.getElementById('twr-photos-json');
  if (photosScript) {
    try { pgPhotos = JSON.parse(photosScript.textContent || '[]'); } catch (e) {}
  }

  const starSvgPg = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';

  function openPhotoGallery(startFilter) {
    if (!pgOverlay || !pgPhotos.length) return;
    pgFilter = startFilter || 'all';
    renderPgTabs();
    renderPgGrid();
    pgOverlay.classList.add('twr-open');
    pgOverlay.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  }

  function closePhotoGallery() {
    if (!pgOverlay) return;
    pgOverlay.classList.remove('twr-open');
    pgOverlay.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  }

  function renderPgTabs() {
    if (!pgTabs) return;
    const ratingSet = new Set(pgPhotos.map(p => p.rating));
    const tabs = [{ key: 'all', label: 'Tất cả' }];
    [5, 4, 3, 2, 1].forEach(r => {
      if (ratingSet.has(r)) tabs.push({ key: String(r), label: r });
    });

    pgTabs.innerHTML = tabs.map(t =>
      `<button class="twr-pg-tab${pgFilter === t.key ? ' twr-pg-tab--active' : ''}" data-filter="${escHtml(t.key)}">` +
      (t.key === 'all' ? 'Tất cả' : `${t.label} ${starSvgPg}`) +
      `</button>`
    ).join('');

    $$('.twr-pg-tab', pgTabs).forEach(btn => {
      btn.addEventListener('click', () => {
        pgFilter = btn.dataset.filter;
        $$('.twr-pg-tab', pgTabs).forEach(b => b.classList.remove('twr-pg-tab--active'));
        btn.classList.add('twr-pg-tab--active');
        renderPgGrid();
      });
    });
  }

  function renderPgGrid() {
    if (!pgGrid) return;
    const items = pgFilter === 'all'
      ? pgPhotos
      : pgPhotos.filter(p => String(p.rating) === pgFilter);

    if (pgTitle) pgTitle.textContent = `${pgPhotos.length} ảnh từ khách hàng`;

    if (!items.length) {
      pgGrid.innerHTML = '<p class="twr-pg-empty">Không có ảnh nào.</p>';
      return;
    }

    pgGrid.innerHTML = items.map((photo, i) => {
      const stars = Array.from({ length: 5 }, (_, si) =>
        `<span class="twr-star${si < photo.rating ? ' twr-star--filled' : ''}">${starSvgPg}</span>`
      ).join('');
      return `<button class="twr-pg-item" data-pg-idx="${i}">` +
               `<img src="${escHtml(photo.url)}" loading="lazy" alt="">` +
               `<div class="twr-pg-item__stars twr-stars">${stars}</div>` +
             `</button>`;
    }).join('');

    $$('.twr-pg-item', pgGrid).forEach(itemBtn => {
      itemBtn.addEventListener('click', () => {
        const idx   = parseInt(itemBtn.dataset.pgIdx, 10);
        const photo = items[idx];
        if (!photo) return;

        closePhotoGallery();

        const vtext = cfg.show_verified ? (cfg.verified_text || '') : '';
        const reviewGallery = items.map(p => ({
          author:        p.author   || '',
          avatar:        p.avatar   || '',
          rating:        p.rating   || 0,
          content:       p.content  || '',
          verified:      p.verified || false,
          verified_text: vtext,
          date:          p.date     || '',
        }));

        // Pass all filtered photos as gallery so prev/next works + syncs review panel
        openLightbox(items.map(p => p.url), idx, reviewGallery[idx], true, reviewGallery);
      });
    });
  }

  // Open gallery when clicking summary photo grid
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-gallery-open]');
    if (!btn) return;
    openPhotoGallery('all');
  });

  pgClose && pgClose.addEventListener('click', closePhotoGallery);
  pgOverlay && pgOverlay.addEventListener('click', (e) => {
    if (e.target === pgOverlay) closePhotoGallery();
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && pgOverlay && pgOverlay.classList.contains('twr-open')) {
      closePhotoGallery();
    }
  });

  /* ── Masonry layout engine ── */
  // grid-auto-rows: 1px, row-gap: 0, margin-bottom: 12px on items
  // span = ceil(naturalHeight) + 12 so next item starts after the gap
  function applyMasonry(container) {
    if (!container || !container.classList.contains('twr-list--masonry')) return;
    const items = Array.from(container.children);
    items.forEach(item => { item.style.gridRowEnd = 'auto'; });
    requestAnimationFrame(() => {
      items.forEach(item => {
        const h = Math.ceil(item.getBoundingClientRect().height);
        if (h > 0) item.style.gridRowEnd = 'span ' + (h + 12);
      });
    });
  }

  function applyMasonryWhenReady(container) {
    if (!container || !container.classList.contains('twr-list--masonry')) return;
    applyMasonry(container);
    const imgs = Array.from(container.querySelectorAll('img')).filter(img => !img.complete);
    if (!imgs.length) return;
    let remaining = imgs.length;
    imgs.forEach(img => {
      const done = () => { remaining--; if (remaining === 0) applyMasonry(container); };
      img.addEventListener('load',  done, { once: true });
      img.addEventListener('error', done, { once: true });
    });
  }

  if (list) {
    applyMasonryWhenReady(list);
    let resizeTimer;
    window.addEventListener('resize', () => {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(() => applyMasonry(list), 150);
    });
  }
})();
