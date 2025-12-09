import {ref, readonly} from 'vue';
import {prefersSchemeInjectionKey} from '@/store/keys.js';

const pScheme = ref(null);
const pSchemeInv = ref(null);

export default {
    install(app) {
        const setScheme = (isDark) => {
            pScheme.value = isDark ? 'dark' : 'light';
            pSchemeInv.value = isDark ? 'light' : 'dark';
        }

        setScheme(window.matchMedia('(prefers-color-scheme: dark)').matches);
        window.matchMedia('(prefers-color-scheme: dark)')
            .addEventListener('change', (event) => {
                setScheme(event.matches);
            });

        app.provide(prefersSchemeInjectionKey, readonly({
            color: pScheme,
            invert: pSchemeInv
        }));
    }
}