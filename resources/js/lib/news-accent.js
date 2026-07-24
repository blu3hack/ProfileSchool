/**
 * Peta aksen berita → kelas neon.
 *
 * Dipakai bersama oleh slider di landing (NeonCoverflow), kartu berita
 * (NewsCard), dan halaman detail, supaya satu kategori selalu tampil
 * dengan warna yang sama di seluruh situs.
 */
const accents = {
    mint: {
        media: 'from-aqua-500/35 via-void-800 to-void-900',
        badge: 'border-aqua-400/50 bg-aqua-400/15 text-aqua-200',
        chip: 'border-aqua-400/40 bg-aqua-400/12 text-aqua-200',
        glow: 'neon-aqua',
        link: 'text-aqua-300',
        hoverTitle: 'group-hover:text-aqua-200',
        text: 'text-aqua-300',
        rule: 'bg-aqua-400',
        glare: 'rgba(124, 243, 255, 0.3)',
        countdownGlow: 'drop-shadow-[0_0_18px_rgba(52,226,245,0.55)]',
    },
    gold: {
        media: 'from-solar-400/35 via-void-800 to-void-900',
        badge: 'border-solar-400/50 bg-solar-400/15 text-solar-300',
        chip: 'border-solar-400/40 bg-solar-400/12 text-solar-300',
        glow: 'neon-solar',
        link: 'text-solar-300',
        hoverTitle: 'group-hover:text-solar-300',
        text: 'text-solar-300',
        rule: 'bg-solar-400',
        glare: 'rgba(255, 199, 61, 0.28)',
        countdownGlow: 'drop-shadow-[0_0_18px_rgba(255,199,61,0.5)]',
    },
    sky: {
        media: 'from-volt-400/35 via-void-800 to-void-900',
        badge: 'border-volt-400/50 bg-volt-400/15 text-volt-300',
        chip: 'border-volt-400/40 bg-volt-400/12 text-volt-300',
        glow: 'neon-volt',
        link: 'text-volt-300',
        hoverTitle: 'group-hover:text-volt-300',
        text: 'text-volt-300',
        rule: 'bg-volt-400',
        glare: 'rgba(169, 123, 255, 0.3)',
        countdownGlow: 'drop-shadow-[0_0_18px_rgba(169,123,255,0.55)]',
    },
    lilac: {
        media: 'from-plasma-400/35 via-void-800 to-void-900',
        badge: 'border-plasma-400/50 bg-plasma-400/15 text-plasma-300',
        chip: 'border-plasma-400/40 bg-plasma-400/12 text-plasma-300',
        glow: 'neon-plasma',
        link: 'text-plasma-300',
        hoverTitle: 'group-hover:text-plasma-300',
        text: 'text-plasma-300',
        rule: 'bg-plasma-400',
        glare: 'rgba(255, 94, 207, 0.3)',
        countdownGlow: 'drop-shadow-[0_0_18px_rgba(255,94,207,0.55)]',
    },
};

export const newsAccent = (name) => accents[name] ?? accents.mint;
