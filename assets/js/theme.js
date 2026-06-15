// assets/js/theme.js
//Fitur super elegan yang mengecek apakah laptop pengunjung sedang memakai Mode Terang atau Mode Gelap. Hebatnya lagi, file ini akan menyimpan pilihan mode tersebut di memori (Local Storage), sehingga jika user mematikan laptop dan membuka web ini lagi besok, webnya masih mengingat warna tema kesukaan mereka.
// Define the two palettes

const lightPalette = {
    white: '#0f172a', // Deep charcoal for white text classes
    slate: { 
        900: '#fafcff', // Warm milky white (Page Background)
        800: '#ffffff', // Card background
        700: '#e2e8f0', // Borders and dividers
        600: '#cbd5e1', 
        500: '#64748b', // Darkened for WCAG placeholder contrast (formerly #94a3b8)
        400: '#475569', // Darkened for WCAG secondary text (formerly #64748b)
        300: '#334155', // Darkened for WCAG
        200: '#0f172a', // Main Text color (Deep Charcoal)
        50: '#020617'
    }, 
    indigo: { 
        300: '#a5b4fc', 
        400: '#818cf8', 
        500: '#635bff', // Stripe Blurple
        600: '#5449ed', 
        900: '#312e81' 
    }, 
    emerald: { 
        400: '#34d399', 
        500: '#10b981', 
        600: '#059669' 
    }
};

const darkPalette = {
    white: '#ffffff',
    slate: {
        900: '#0a0710', // Cyber Midnight Background
        800: '#130d26', // Cyber Midnight Card
        700: '#2a224a', // Dark Purple Borders
        600: '#3f3566',
        500: '#64598c',
        400: '#8a82a8', // Cyber Secondary Text
        300: '#b1abc4',
        200: '#f8fafc', // Main Text color
        50: '#ffffff'
    },
    indigo: { // Shifted to Neon Purple/Pink for Cyber aesthetic
        300: '#d8b4fe', 
        400: '#c084fc', 
        500: '#a855f7', // Neon Purple
        600: '#9333ea', 
        900: '#3b0764' 
    }, 
    emerald: { // Shifted to Cyan for Cyber aesthetic
        400: '#22d3ee', // Neon Cyan
        500: '#06b6d4', 
        600: '#0891b2' 
    }
};

// Check localStorage
let currentTheme = localStorage.getItem('theme') || 'light';

// Apply class to html for CSS variables (like --glass-bg)
if (currentTheme === 'dark') {
    document.documentElement.classList.add('dark');
} else {
    document.documentElement.classList.remove('dark');
}

// Inject tailwind config
tailwind.config = { 
    darkMode: 'class',
    theme: { 
        extend: { 
            fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] }, 
            colors: currentTheme === 'dark' ? darkPalette : lightPalette
        } 
    } 
};

// Function to toggle theme
window.toggleTheme = function() {
    const isDark = document.documentElement.classList.contains('dark');
    const newTheme = isDark ? 'light' : 'dark';
    
    // Save to localStorage
    localStorage.setItem('theme', newTheme);
    
    // Reload page to apply new Tailwind config (since CDN parses once on load)
    // Wait for a smooth fade out
    document.body.style.transition = 'opacity 0.3s ease';
    document.body.style.opacity = '0';
    setTimeout(() => {
        window.location.reload();
    }, 300);
};
