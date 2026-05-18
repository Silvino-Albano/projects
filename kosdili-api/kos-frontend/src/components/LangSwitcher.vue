<template>
  <div class="lang-switcher" ref="switcher">

    <!-- Tombol aktif -->
    <button class="lang-btn" @click="open = !open" :title="currentLang.label">
      <span class="lang-flag">{{ currentLang.flag }}</span>
      <span class="lang-code">{{ currentLang.code.toUpperCase() }}</span>
      <svg class="lang-arrow" :class="{ rotated: open }" viewBox="0 0 12 12" fill="none">
        <path d="M2 4l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </button>

    <!-- Dropdown pilihan bahasa -->
    <transition name="dropdown">
      <div v-if="open" class="lang-dropdown">
        <button
          v-for="lang in languages"
          :key="lang.code"
          class="lang-option"
          :class="{ active: locale === lang.code }"
          @click="selectLang(lang.code)"
        >
          <span class="lang-flag">{{ lang.flag }}</span>
          <span class="lang-name">{{ lang.label }}</span>
          <svg v-if="locale === lang.code" class="lang-check" viewBox="0 0 12 12" fill="none">
            <path d="M2 6l3 3 5-5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
      </div>
    </transition>

  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { setLanguage } from '../i18n.js'

export default {
  name: 'LangSwitcher',
  setup() {
    const { locale } = useI18n()
    const open       = ref(false)
    const switcher   = ref(null)

    const languages = [
      { code: 'id', label: 'Bahasa Indonesia', flag: '🇮🇩' },
      { code: 'en', label: 'English',           flag: '🇬🇧' },
      { code: 'pt', label: 'Português',          flag: '🇵🇹' },
    ]

    const currentLang = computed(() =>
      languages.find(l => l.code === locale.value) || languages[0]
    )

    const selectLang = (code) => {
      setLanguage(code)
      open.value = false
    }

    // Tutup dropdown kalau klik di luar
    const handleClickOutside = (e) => {
      if (switcher.value && !switcher.value.contains(e.target)) {
        open.value = false
      }
    }

    onMounted(() => document.addEventListener('click', handleClickOutside))
    onUnmounted(() => document.removeEventListener('click', handleClickOutside))

    return { locale, open, switcher, languages, currentLang, selectLang }
  }
}
</script>

<style scoped>
.lang-switcher {
  position: relative;
}

/* Tombol aktif */
.lang-btn {
  display: flex;
  align-items: center;
  gap: 5px;
  padding: 6px 10px;
  background: none;
  border: 1px solid #d4c5b0;
  border-radius: 8px;
  cursor: pointer;
  font-family: 'DM Sans', sans-serif;
  font-size: 13px;
  color: #6b5b47;
  transition: background .15s, border-color .15s;
  white-space: nowrap;
}
.lang-btn:hover {
  background: #f0ebe3;
  border-color: #c17f3e;
}
.lang-flag { font-size: 15px; line-height: 1; }
.lang-code { font-weight: 500; font-size: 12px; letter-spacing: .05em; }
.lang-arrow {
  width: 11px;
  height: 11px;
  transition: transform .2s;
  opacity: .6;
}
.lang-arrow.rotated { transform: rotate(180deg); }

/* Dropdown */
.lang-dropdown {
  position: absolute;
  top: calc(100% + 6px);
  right: 0;
  background: #fff;
  border: 1px solid #e8e2d9;
  border-radius: 10px;
  box-shadow: 0 8px 24px rgba(0,0,0,.12);
  overflow: hidden;
  min-width: 175px;
  z-index: 500;
}

.lang-option {
  display: flex;
  align-items: center;
  gap: 8px;
  width: 100%;
  padding: 10px 14px;
  background: none;
  border: none;
  cursor: pointer;
  font-family: 'DM Sans', sans-serif;
  font-size: 13.5px;
  color: #4a3f35;
  text-align: left;
  transition: background .12s;
}
.lang-option:hover { background: #faf8f4; }
.lang-option.active {
  background: #fdf5ec;
  color: #c17f3e;
  font-weight: 500;
}
.lang-name  { flex: 1; }
.lang-check { width: 13px; height: 13px; color: #c17f3e; flex-shrink: 0; }

/* Animasi dropdown */
.dropdown-enter-active,
.dropdown-leave-active {
  transition: opacity .15s ease, transform .15s ease;
}
.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}
</style>
