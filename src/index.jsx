// src/index.jsx
import { render } from '@wordpress/element';
import SettingsApp from './components/SettingsApp';

render(<SettingsApp />, document.getElementById('fm-msrp-settings-root'));

window.addEventListener('DOMContentLoaded', () => {
	const target = document.getElementById('fm-msrp-settings-root');
	if (target) {
		render(<App />, target);
	}
});
