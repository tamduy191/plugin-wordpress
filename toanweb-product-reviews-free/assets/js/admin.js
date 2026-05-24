/* ToanWeb Product Reviews (Free) — Vue 3 Admin */
(function () {
  'use strict';

  const { createApp, ref, reactive, computed } = Vue;

  const PRO_URL = 'https://doxuantoan.com/toanweb-product-reviews';

  const api = {
    base:  window.TWR_ADMIN?.rest_url || '',
    nonce: window.TWR_ADMIN?.nonce   || '',
    async post(endpoint, data) {
      const r = await fetch(this.base + endpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': this.nonce },
        body: JSON.stringify(data),
      });
      return r.json();
    },
  };

  /* ─────────────────────────────────────────
     PRO Teaser Component (iOS-style)
  ───────────────────────────────────────── */
  const ProTeaser = {
    props: ['tab'],
    setup() { return { PRO_URL }; },
    template: `
      <div class="twr-pro-teaser">

        <div class="twr-pro-teaser__lock">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
          </svg>
        </div>

        <div class="twr-pro-teaser__badge">
          <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
            <rect x="3" y="11" width="18" height="11" rx="2"/>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
          </svg>
          CHỈ CÓ Ở BẢN PRO
        </div>

        <h2 class="twr-pro-teaser__title">{{ tab.label }}</h2>
        <p class="twr-pro-teaser__desc">{{ tab.proDesc }}</p>

        <div class="twr-pro-teaser__features">
          <div v-for="f in tab.features" :key="f" class="twr-pro-teaser__feature">
            <span class="twr-pro-teaser__check">
              <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5">
                <polyline points="20 6 9 17 4 12"/>
              </svg>
            </span>
            <span>{{ f }}</span>
          </div>
        </div>

        <a :href="PRO_URL" target="_blank" rel="noopener noreferrer" class="twr-pro-teaser__cta">
          Nâng cấp lên PRO
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="5" y1="12" x2="19" y2="12"/>
            <polyline points="12 5 19 12 12 19"/>
          </svg>
        </a>

        <p class="twr-pro-teaser__footnote">Mua 1 lần &middot; Dùng vĩnh viễn &middot; Không phí ẩn</p>
      </div>
    `,
  };

  /* ─────────────────────────────────────────
     General Tab
  ───────────────────────────────────────── */
  const GeneralTab = {
    props: ['settings'],
    template: `
      <div class="twr-section">
        <h3 class="twr-section__title">Cài đặt chung</h3>

        <div class="twr-group">
          <h4 class="twr-group__title">Kích hoạt</h4>
          <div class="twr-field-row">
            <div class="twr-field-info">
              <label class="twr-field-label">Bật plugin</label>
              <span class="twr-field-desc">Hiển thị section đánh giá tùy chỉnh trên trang sản phẩm</span>
            </div>
            <label class="twr-toggle">
              <input type="checkbox" v-model="settings.enabled">
              <span class="twr-toggle__track"></span>
            </label>
          </div>
        </div>

        <div class="twr-group">
          <h4 class="twr-group__title">Giao diện</h4>
          <div class="twr-field-row">
            <div class="twr-field-info">
              <label class="twr-field-label">Màu chủ đạo</label>
              <span class="twr-field-desc">Màu của nút, filter, thanh progress</span>
            </div>
            <div class="twr-color-pick">
              <input type="color" v-model="settings.primary_color" class="twr-color-input">
              <input type="text"  v-model="settings.primary_color" class="twr-input twr-input--sm" maxlength="7" placeholder="#64b2fa">
            </div>
          </div>
        </div>

        <div class="twr-group">
          <h4 class="twr-group__title">Kiểm duyệt</h4>
          <div class="twr-field-row">
            <div class="twr-field-info">
              <label class="twr-field-label">Phê duyệt thủ công</label>
              <span class="twr-field-desc">Đánh giá mới sẽ ở trạng thái "Chờ duyệt" — admin vào WP Admin → Bình luận để duyệt</span>
            </div>
            <label class="twr-toggle">
              <input type="checkbox" v-model="settings.require_approval">
              <span class="twr-toggle__track"></span>
            </label>
          </div>
          <div class="twr-field-row">
            <div class="twr-field-info">
              <label class="twr-field-label">Chặn link trong đánh giá</label>
              <span class="twr-field-desc">Từ chối đánh giá có chứa URL (http/https) — chống spam link</span>
            </div>
            <label class="twr-toggle">
              <input type="checkbox" v-model="settings.block_links_in_review">
              <span class="twr-toggle__track"></span>
            </label>
          </div>
        </div>

        <div class="twr-group">
          <h4 class="twr-group__title">Badge xác nhận</h4>
          <div class="twr-field-row">
            <div class="twr-field-info">
              <label class="twr-field-label">Hiển thị "Đã mua hàng"</label>
              <span class="twr-field-desc">Badge xác nhận đã mua hàng bên cạnh tên reviewer</span>
            </div>
            <label class="twr-toggle">
              <input type="checkbox" v-model="settings.show_verified">
              <span class="twr-toggle__track"></span>
            </label>
          </div>
          <div class="twr-field-row" v-if="settings.show_verified">
            <div class="twr-field-info">
              <label class="twr-field-label">Nội dung badge</label>
              <span class="twr-field-desc">Ví dụ: "Đã mua tại ToanWeb", "Đã mua tại cửa hàng"</span>
            </div>
            <input type="text" v-model="settings.verified_text"
                   class="twr-input twr-input--md"
                   placeholder="Đã mua tại cửa hàng">
          </div>
        </div>

      </div>
    `,
  };

  /* ─────────────────────────────────────────
     Root App
  ───────────────────────────────────────── */
  const App = {
    template: `
      <div class="twr-admin">
        <div class="twr-admin__header">
          <div class="twr-admin__logo">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
            </svg>
            <span>ToanWeb Product Reviews</span>
          </div>
          <button class="twr-btn-save" @click="save" :disabled="saving || activeTab !== 'general'">
            <span v-if="saving">Đang lưu...</span>
            <span v-else>Lưu cài đặt</span>
          </button>
        </div>

        <div v-if="toast.show" class="twr-toast" :class="'twr-toast--' + toast.type">
          {{ toast.message }}
        </div>

        <div class="twr-admin__body">
          <div class="twr-tabs">
            <button
              v-for="tab in tabs" :key="tab.id"
              class="twr-tab"
              :class="{ 'twr-tab--active': activeTab === tab.id, 'twr-tab--pro': tab.pro }"
              @click="activeTab = tab.id"
            >
              <span v-html="tab.icon" class="twr-tab__icon"></span>
              <span class="twr-tab__label">{{ tab.label }}</span>
              <span v-if="tab.pro" class="twr-tab__pro-badge">PRO</span>
            </button>
          </div>

          <div class="twr-panel">
            <general-tab v-if="activeTab === 'general'" :settings="settings" />
            <pro-teaser  v-else :tab="currentTab" />
          </div>
        </div>
      </div>
    `,

    setup() {
      const settings  = reactive(JSON.parse(JSON.stringify(window.TWR_ADMIN?.settings || {})));
      const activeTab = ref('general');
      const saving    = ref(false);
      const toast     = reactive({ show: false, type: 'success', message: '' });

      const tabs = [
        {
          id: 'general', label: 'Chung', pro: false,
          icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>',
        },
        {
          id: 'display', label: 'Hiển thị', pro: true,
          icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 9h6M9 12h6M9 15h4"/></svg>',
          proDesc: 'Tuỳ chỉnh hoàn toàn giao diện hiển thị đánh giá: layout, phân trang, filter, lightbox ảnh và nhiều hơn nữa.',
          features: [
            'Layout: danh sách, lưới (grid), masonry tự động',
            'Filter bar kiểu TikTok hoặc dạng text',
            'Số đánh giá/trang & kiểu tải (loadmore / phân trang)',
            'Lightbox xem ảnh full-screen',
            'Tắt tab đánh giá mặc định của WooCommerce',
            'Upload ảnh/video — tùy chỉnh giới hạn dung lượng',
          ],
        },
        {
          id: 'criteria', label: 'Tiêu chí', pro: true,
          icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>',
          proDesc: 'Hiển thị đánh giá đa chiều theo từng tiêu chí cụ thể như Hiệu năng, Pin, Camera — tăng độ tin cậy vượt trội.',
          features: [
            'Tạo không giới hạn tiêu chí đánh giá (Hiệu năng, Pin...)',
            'Điểm trung bình từng tiêu chí trong phần tóm tắt',
            'Tag label theo mức sao (1–5) cho từng tiêu chí',
            'Kéo thả sắp xếp thứ tự tiêu chí',
            'Hiển thị trong form đánh giá của khách hàng',
          ],
        },
        {
          id: 'coupon', label: 'Coupon', pro: true,
          icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>',
          proDesc: 'Tự động gửi mã giảm giá cho khách hàng sau khi họ để lại đánh giá — tăng tỷ lệ viết review lên đáng kể.',
          features: [
            'Tự động email mã coupon sau khi đánh giá được duyệt',
            'Cấu hình số sao tối thiểu để nhận mã (VD: từ 4 sao)',
            'Yêu cầu đính kèm ảnh mới được nhận coupon',
            'Dùng bất kỳ mã giảm giá WooCommerce nào',
          ],
        },
        {
          id: 'ai', label: 'AI', pro: true,
          icon: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/></svg>',
          proDesc: 'Tạo hàng chục đánh giá chân thực bằng AI chỉ trong vài giây — hỗ trợ Claude, Gemini, ChatGPT và DeepSeek.',
          features: [
            'Tích hợp Claude, Gemini, ChatGPT, DeepSeek',
            'Tạo hàng loạt với phân bổ rating tự nhiên',
            'Tùy chỉnh phong cách nội dung & tên tác giả',
            'Đánh giá đa dạng — không trùng lặp, không rõ AI',
            'Xem trước và chỉnh sửa trước khi lưu',
          ],
        },
      ];

      const currentTab = computed(() => tabs.find(t => t.id === activeTab.value));

      function showToast(type, message) {
        toast.show = true; toast.type = type; toast.message = message;
        setTimeout(() => { toast.show = false; }, 3000);
      }

      async function save() {
        saving.value = true;
        try {
          const result = await api.post('settings', JSON.parse(JSON.stringify(settings)));
          if (result && !result.message) {
            Object.assign(settings, result);
            showToast('success', 'Đã lưu cài đặt thành công!');
          } else {
            showToast('error', result?.message || 'Có lỗi xảy ra.');
          }
        } catch (e) {
          showToast('error', 'Không thể kết nối server.');
        } finally {
          saving.value = false;
        }
      }

      return { settings, activeTab, tabs, currentTab, saving, toast, save };
    },
  };

  const app = createApp(App);
  app.component('general-tab', GeneralTab);
  app.component('pro-teaser',  ProTeaser);
  app.mount('#twr-admin-app');
})();
