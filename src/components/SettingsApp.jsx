import ListPriceLabelField from './ListPriceLabelField';

export default function SettingsApp() {
  return (
    <div className="fm-msrp-admin" style={{ margin: '25px 15px 2px' }}>
      <h1>FM MSRP Settings</h1>
      <ListPriceLabelField />
    </div>
  );
}
