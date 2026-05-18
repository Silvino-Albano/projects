// src/i18n.js
import { createI18n } from 'vue-i18n'
import id from './locales/id.js'
import en from './locales/en.js'
import pt from './locales/pt.js'

// Ambil bahasa yang tersimpan di localStorage
// Kalau belum ada, default ke Bahasa Indonesia
const savedLang = localStorage.getItem('kosdili_lang') || 'id'

export const i18n = createI18n({
  legacy      : false,       // wajib false untuk Vue 3 Composition API
  locale      : savedLang,
  fallbackLocale: 'id',      // kalau terjemahan tidak ada, fallback ke Indonesia
  messages    : { id, en, pt },
})

// Helper untuk ganti bahasa dari mana saja
export function setLanguage(lang) {
  i18n.global.locale.value = lang
  localStorage.setItem('kosdili_lang', lang)
  document.documentElement.setAttribute('lang', lang)
}
