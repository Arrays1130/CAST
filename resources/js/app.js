import './bootstrap';

import Alpine from 'alpinejs';
import persist from '@alpinejs/persist';
import focus from '@alpinejs/focus';
import collapse from '@alpinejs/collapse';

Alpine.plugin(persist);
Alpine.plugin(focus);
Alpine.plugin(collapse);

Alpine.data('countUp', (target) => ({
    value: 0,
    init() {
        const end = Number(target) || 0;
        if (end === 0) {
            return;
        }
        const step = Math.max(1, Math.ceil(end / 18));
        const tick = () => {
            this.value = Math.min(end, this.value + step);
            if (this.value < end) {
                requestAnimationFrame(tick);
            }
        };
        requestAnimationFrame(tick);
    },
}));

window.castDriveUrl = (raw) => {
    if (! raw || typeof raw !== 'string') {
        return null;
    }

    try {
        const url = new URL(raw.trim());
        if (! url.hostname.toLowerCase().includes('google.com')) {
            return null;
        }

        const path = url.pathname;
        const patterns = [
            [/\/file\/d\/([a-zA-Z0-9_-]+)/, (id) => `https://drive.google.com/file/d/${id}/view`],
            [/\/document\/d\/([a-zA-Z0-9_-]+)/, (id) => `https://docs.google.com/document/d/${id}/edit`],
            [/\/presentation\/d\/([a-zA-Z0-9_-]+)/, (id) => `https://docs.google.com/presentation/d/${id}/edit`],
            [/\/spreadsheets\/d\/([a-zA-Z0-9_-]+)/, (id) => `https://docs.google.com/spreadsheets/d/${id}/edit`],
            [/\/folders\/([a-zA-Z0-9_-]+)/, (id) => `https://drive.google.com/drive/folders/${id}`],
        ];

        for (const [pattern, toUrl] of patterns) {
            const match = path.match(pattern);
            if (match) {
                return toUrl(match[1]);
            }
        }

        const id = url.searchParams.get('id');
        if (id) {
            return `https://drive.google.com/file/d/${id}/view`;
        }
    } catch {
        return null;
    }

    return null;
};

Alpine.data('uploadModal', (initial = {}) => ({
    source: initial.source || 'file',
    busy: false,
    over: false,
    name: '',
    title: initial.title || '',
    driveUrl: initial.driveUrl || '',
    fileHref() {
        return window.castDriveUrl(this.driveUrl);
    },
    setFile(file) {
        if (! file) {
            return;
        }
        const transfer = new DataTransfer();
        transfer.items.add(file);
        this.$refs.input.files = transfer.files;
        this.name = file.name;
        if (! this.title.trim()) {
            this.title = file.name.replace(/\.[^.]+$/, '').replace(/[-_]+/g, ' ');
        }
    },
    openGoogleDrive() {
        this.source = 'drive';
        window.open(this.fileHref() || 'https://drive.google.com/drive/my-drive', '_blank', 'noopener');
    },
}));

Alpine.data('dropzone', () => ({
    name: '',
    over: false,
    setFile(file) {
        if (! file) {
            return;
        }
        const transfer = new DataTransfer();
        transfer.items.add(file);
        this.$refs.input.files = transfer.files;
        this.name = file.name;
    },
}));

Alpine.data('commandPalette', (links) => ({
    open: false,
    nav: false,
    q: '',
    links,
    get results() {
        const q = this.q.toLowerCase();
        return this.links.filter((item) => item.label.toLowerCase().includes(q) || item.hint.toLowerCase().includes(q));
    },
    init() {
        this.$watch('open', (value) => {
            if (value) {
                this.$nextTick(() => this.$refs.search?.focus());
            }
        });
        window.addEventListener('keydown', (event) => {
            if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
                event.preventDefault();
                this.open = ! this.open;
                this.q = '';
            }
            if (event.key === 'Escape') {
                this.open = false;
            }
        });
    },
    go(href) {
        this.open = false;
        window.location.href = href;
    },
}));

window.Alpine = Alpine;
Alpine.start();
