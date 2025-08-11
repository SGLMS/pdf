# SglmsPdf (Laravel Mpdf Wrapper)

Simple Laravel (^12.0) wrapper for Mpdf, using Laravel's view components.

## Installation

```php
composer require sglms/pdf
```

Laravel's auto-discovery features will register the service provider and facade.


## Usage

For the impatient:

```php
use Sglms\Pdf;

$pdf = Pdf::view('pdf.filename')->output('filename');
```

### View Parameters


```php
use Sglms\Pdf;

$pdf = Pdf::view(
	'pdf.filename',
	['param' => 'value']	/* [Optional */
);
$pdf->output('filename');

/* Or, ... */

$pdf->save('filename');
```

### Configuration and Header/Footer


```php
$pdf = Pdf::init(
	config: ['format' => 'letter'],
	header: 'pdf.header',
	footer: 'pdf.footer'
	stylesheet: 'path/to/stylesheet.css'
);
```
Add your logo (available as 'var:logo' in your views):

```php
$pdf->logo('path/to/logo.svg');
```

Remember to add your logo before header/footer setup, if you plan to use them there.

You can override setup if you need parameters:

```php
$pdf->header('pdf.header', ['param' => 'value']);
$pdf->footer('pdf.footer', ['param' => 'value']);
```

Include your header **before** adding views. This is a limitation of mPDF in that it calls `AddPage()` when you include a view (or any other html), and if the header is not set, mpdf will render it blank.


Concatenate multiple views:

```php
$pdf = Pdf::init();
$pdf->view('pdf.one')->view('pdf.two')->output('filename.pdf');
```

Sign your document:

```php
$pdf->sign('pdf.signature', ['name' => 'John Doe']);
```

Or, ...


```php
$pdf->sign(
	signature: 'pdf.signature',
	data: ['name' => 'John Doe'],
	x: 50,		/* [mm] */
	y: 100,		/* [mm] */
	width: 100	/* [mm] */
);
```

If you need the base mPDF to work on it further:

```php
$pdf->get();
```



### Use it in your controller

```php
use Sglms\Pdf;

class CustomController extendes Controller
{
	// ...
	public function render() {
        $pdf = Pdf::view('pdf.filename')->sign('pdf.signature');
        return response($pdf->output(), 200)
        	->header('Content-Type', 'application/pdf')
         	->header('Content-Disposition', 'inline; filename="document.pdf"');
    }
    //...
}
```



## Limitations

As mentioned, very simple (but efficient) wrapper, that works with Laravel 12 (php >=8.3; mpdf >= 8.x).

It's better suited for single page document generation.

### Rationale

This package is part of a larger project in which we needed to generate a large number of one-page documents and (digitally) sign them.

## License

SglmsPdf is licensed under the The MIT License.