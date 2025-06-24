import { render } from '@wordpress/element';

const App = () => {
	return (
		<div>
			<h1>FM MSRP Settings</h1>
			<p>This is the beginning of your React-powered settings screen.</p>
		</div>
	);
};

window.addEventListener('DOMContentLoaded', () => {
	const target = document.getElementById('fm-msrp-settings-root');
	if (target) {
		render(<App />, target);
	}
});
