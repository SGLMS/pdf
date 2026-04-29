# SGLMS PDF

Simple Laravel wrapper around mPDF with a fluent API for rendering Blade views, attaching headers/footers, and signing documents.

## Requirements

- PHP >= 8.3
- Laravel 12
- mPDF 8.2+

## Installation

```bash
composer require sglms/pdf
```

Laravel package discovery registers the service provider and facade automatically.

## Quick Start

```php
use Sglms\Pdf\Facades\Pdf;

Pdf::view('pdf.invoice', ['order' => $order])->output('invoice.pdf');
```

## Usage

### Initialize with configuration

```php
use Sglms\Pdf\Facades\Pdf;

$pdf = Pdf::init(
	config: ['format' => 'Letter'],
	stylesheet: 'css/pdf.css',
	header: 'pdf.header',
	footer: 'pdf.footer'
);
```

### Render one or multiple views

```php
$pdf->view('pdf.page-one', ['name' => 'Jane'])
	->view('pdf.page-two')
	->output('document.pdf');
```

### Add logo for header/footer views

The logo is available in mPDF templates as `var:logo`.

```php
$pdf->logo('images/logo.svg');
```

If your header or footer uses the logo, call `logo()` before `header()` / `footer()`.

### Add signature block

```php
$pdf->sign(
	signature: 'pdf.signature',
	data: ['name' => 'John Doe'],
	x: 50,
	y: 100,
	width: 100
);
```

### Sign existing PDF

```php
$pdf->signFile('pdf.signature', storage_path('app/base.pdf'));
```

### Output options

```php
$pdf->output('document.pdf');          // Inline in browser
$pdf->download('document.pdf');        // Force download
$pdf->save(storage_path('app/a.pdf')); // Write to file path
$pdf->storeAs('a.pdf', 'public');      // Laravel Storage disk
$raw = $pdf->string();                 // Raw PDF string
```

### Access underlying mPDF instance

```php
$mpdf = $pdf->get();
```

## Controller Example

```php
use App\Http\Controllers\Controller;
use Sglms\Pdf\Facades\Pdf;

class CustomController extends Controller
{
	public function render()
	{
		Pdf::view('pdf.filename')
			->sign('pdf.signature')
			->output('document.pdf');
	}
}
```

## Notes

- mPDF applies headers/footers from the point they are set. Set header/footer before the first rendered body view when possible.
- This package is intentionally lightweight and optimized for straightforward document-generation workflows.

## License

MIT
