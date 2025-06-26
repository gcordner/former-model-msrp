import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

export default function CustomCssField() {
  const [css, setCss] = useState('');
  const [saving, setSaving] = useState(false);

  // Load saved custom CSS on mount
  useEffect(() => {
    apiFetch({ path: '/fm-msrp/v1/settings' }).then((data) => {
      if (data.custom_css !== undefined) {
        setCss(data.custom_css);
      }
    });
  }, []);

  const handleSave = () => {
    setSaving(true);
    apiFetch({
      path: '/fm-msrp/v1/settings',
      method: 'POST',
      data: { custom_css: css },
    }).then(() => setSaving(false));
  };

  return (
    <div style={{ marginTop: '2rem' }}>
      <label htmlFor="fm-msrp-css">
        <strong>Custom CSS</strong>
      </label>
      <br />
      <textarea
        id="fm-msrp-css"
        value={css}
        onChange={(e) => setCss(e.target.value)}
        rows="8"
        style={{ width: '100%', maxWidth: '700px', marginTop: '0.5rem', fontFamily: 'monospace' }}
        placeholder={`/* Your custom styles here */\n.fm-msrp { color: red; }`}
      />
      <br />
      <button onClick={handleSave} disabled={saving} style={{ marginTop: '0.5rem' }}>
        {saving ? 'Saving…' : 'Save'}
      </button>
    </div>
  );
}
