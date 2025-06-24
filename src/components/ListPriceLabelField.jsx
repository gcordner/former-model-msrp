// src/components/ListPriceLabelField.jsx
import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

export default function ListPriceLabelField() {
  const [label, setLabel] = useState('');
  const [saving, setSaving] = useState(false);

  // Load saved option
  useEffect(() => {
    apiFetch({ path: '/fm-msrp/v1/settings' }).then((data) => {
      if (data.label) setLabel(data.label);
    });
  }, []);

  const handleSave = () => {
    setSaving(true);
    apiFetch({
      path: '/fm-msrp/v1/settings',
      method: 'POST',
      data: { label },
    }).then(() => setSaving(false));
  };

  return (
    <div style={{ marginTop: '1rem' }}>
      <label htmlFor="fm-msrp-label"><strong>List Price Label</strong></label><br />
      <input
        type="text"
        id="fm-msrp-label"
        value={label}
        onChange={(e) => setLabel(e.target.value)}
        style={{ width: '300px', marginTop: '0.5rem' }}
      />
      <br />
      <button onClick={handleSave} disabled={saving} style={{ marginTop: '0.5rem' }}>
        {saving ? 'Saving…' : 'Save'}
      </button>
    </div>
  );
}
// This component allows the admin to set a custom label for the list price. It fetches the current label from the server when mounted, and provides a text input for editing it. When the "Save" button is clicked, it sends the updated label back to the server using the WordPress REST API. The button is disabled while saving to prevent multiple submissions.