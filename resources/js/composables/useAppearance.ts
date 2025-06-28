import { onMounted, ref } from 'vue';

type Appearance = 'light' | 'system';

export function updateTheme(value: Appearance) {
    if (typeof window === 'undefined') {
        return;
    }

    if (value === 'system') {
        const mediaQueryList = window.matchMedia('(prefers-color-scheme: dark)');
        const systemTheme = mediaQueryList.matches ? 'dark' : 'light';

        // Force light theme even if system prefers dark
        document.documentElement.classList.remove('dark');
    } else {
        // Only allow light theme
        document.documentElement.classList.remove('dark');
    }
}

const setCookie = (name: string, value: string, days = 365) => {
    if (typeof document === 'undefined') {
        return;
    }

    const maxAge = days * 24 * 60 * 60;

    document.cookie = `${name}=${value};path=/;max-age=${maxAge};SameSite=Lax`;
};

const mediaQuery = () => {
    if (typeof window === 'undefined') {
        return null;
    }

    return window.matchMedia('(prefers-color-scheme: dark)');
};

const getStoredAppearance = () => {
    if (typeof window === 'undefined') {
        return null;
    }

    return localStorage.getItem('appearance') as Appearance | null;
};

const handleSystemThemeChange = () => {
    const currentAppearance = getStoredAppearance();

    updateTheme(currentAppearance || 'system');
};

export function initializeTheme() {
    if (typeof window === 'undefined') {
        return;
    }

    // Force light theme on initialization
    document.documentElement.classList.remove('dark');

    // Initialize theme from saved preference or default to system...
    const savedAppearance = getStoredAppearance();
    updateTheme(savedAppearance || 'system');

    // Set up system theme change listener...
    mediaQuery()?.addEventListener('change', handleSystemThemeChange);
}

const appearance = ref<Appearance>('light'); // Default to light

export function useAppearance() {
    onMounted(() => {
        const savedAppearance = localStorage.getItem('appearance') as Appearance | null;

        if (savedAppearance && savedAppearance !== 'dark') {
            appearance.value = savedAppearance;
        } else {
            // If saved preference was dark, reset to light
            appearance.value = 'light';
            localStorage.setItem('appearance', 'light');
            setCookie('appearance', 'light');
        }
    });

    function updateAppearance(value: Appearance) {
        // Filter out any dark mode attempts
        if (value === 'dark') {
            value = 'light';
        }

        appearance.value = value;

        // Store in localStorage for client-side persistence...
        localStorage.setItem('appearance', value);

        // Store in cookie for SSR...
        setCookie('appearance', value);

        updateTheme(value);
    }

    return {
        appearance,
        updateAppearance,
    };
}
