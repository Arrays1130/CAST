import { createRoot } from 'react-dom/client';
import LandingApp from './landing/LandingApp.jsx';

const root = document.getElementById('landing-react-root');

if (root) {
    let pages = [];
    try {
        pages = JSON.parse(root.dataset.scenes || '[]');
    } catch {
        pages = [];
    }

    createRoot(root).render(<LandingApp pages={pages} />);
}
