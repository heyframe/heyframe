/**
 * @sw-package framework
 */
import { watch } from 'vue';

let isInitialized = false;

/**
 * @private
 */
export default function LanguageAutoFetchingService() {
    if (isInitialized) return;
    isInitialized = true;

    // initial loading of the language
    loadLanguage(HeyFrame.Context.api.languageId);

    // load the language Entity
    async function loadLanguage(newLanguageId) {
        const languageRepository = HeyFrame.Service('repositoryFactory').create('language');
        const newLanguage = await languageRepository.get(newLanguageId, {
            ...HeyFrame.Context.api,
            inheritance: true,
        });

        HeyFrame.Store.get('context').api.language = newLanguage;
    }

    watch(HeyFrame.Store.get('context').api.languageId, loadLanguage);
}
