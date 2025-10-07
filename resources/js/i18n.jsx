// src/i18n.js
import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';

import translationEN from './locales/en.json';
import translationDE from './locales/de.json';
import translationFR from './locales/fr.json';
import translationNL from './locales/nl.json';


i18n
    .use(initReactI18next)
    .init({
        resources: {
            en: { translation: translationEN },
            de: { translation: translationDE },
            fr: { translation: translationFR },
            nl: { translation: translationNL },

        },
        lng: 'en',
        fallbackLng: 'en',
        interpolation: {
            escapeValue: false,
        },
    });

export default i18n;
