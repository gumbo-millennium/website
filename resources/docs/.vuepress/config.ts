import {defaultTheme} from '@vuepress/theme-default'
import { viteBundler } from '@vuepress/bundler-vite'
import { defineUserConfig } from 'vuepress'

export default defineUserConfig({
    bundler: viteBundler(),

    lang: 'nl-NL',
    title: 'Gumbo Millennium Website Documentatie',
    description: 'Documentatie voor leden van Gumbo om het maximale te halen uit het beheer van de site.',

    // Set base path
    base: '/docs/',

    // Theming
    theme: defaultTheme({
        // Logo
        logo: '/images/logo-text-green.svg',
        logoDark: '/images/logo-text-white.svg',

        // Navigation
        home: '/docs/',
        navbar: [
            {
                text: 'Home',
                link: '/',
            },
        ],

        // Settings
        contributors: false,
    }),
})
