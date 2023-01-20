import path from 'path'
import { defineUserConfig } from 'vuepress'

export default defineUserConfig({
  lang: 'nl-NL',
  title: 'Gumbo Millennium Website Documentatie',
  description: 'Documentatie voor leden van Gumbo om het maximale te halen uit het beheer van de site.',

  // Paths
  base: '/docs/',
  public: path.join(__dirname, 'resources/docs/assets'),
  dest: path.join(__dirname, 'public/docs'),
})
