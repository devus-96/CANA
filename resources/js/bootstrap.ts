import axios from 'axios';

/**
 * Augmente l'objet global Window pour inclure notre instance axios.
 * Cela est nécessaire pour que TypeScript sache que `window.axios` existe.
 */
declare global {
  interface Window {
    axios: typeof axios;
  }
}

// Assigne l'instance d'axios à l'objet global window.
window.axios = axios;

// Active l'envoi des cookies et des en-têtes d'authentification pour les requêtes cross-site.
window.axios.defaults.withCredentials = true;

// Récupère le jeton CSRF à partir de la balise meta.
const csrfTokenElement = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]');
const csrfToken = csrfTokenElement?.getAttribute('content') ?? '';

// Définit les en-têtes communs pour toutes les requêtes sortantes.
window.axios.defaults.headers.common = {
  'X-Requested-With': 'XMLHttpRequest',
  'X-XSRF-TOKEN': csrfToken,
};
