<?php

namespace Sglms\Pdf;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Traits\Macroable;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

/**
 * PdfService
 *
 * @category Library
 * @package  Sglms/Pdf
 * @author   James <james@sglms.com>
 * @license  https://opensource.org/license/MIT MIT
 * @link     https://sglms.com/
 */
class PdfService
{
    use Macroable;

    protected Mpdf $pdf;
    protected array $conf = [];

    /**
     * Constructor
     *
     * @param array|null  $conf  Configuration
     * @param string|null $title PDF title
     */
    public function __construct(
        ?array $conf      = [],
        ?string $title    = null,
    ) {
        $this->conf = array_merge($this->defaultConfig(), $conf ?? []);

        $config = $this->conf;
        if ($title !== null) {
            $config['title'] = $title;
        }

        $this->pdf = new Mpdf($config);
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultConfig(): array
    {
        return [
            'format'            => 'Letter',
            'default_font'      => 'sans',
            'default_font_size' => 10,
            'margin_top'        => 25,
            'margin_left'       => 25,
            'margin_right'      => 25,
            'margin_bottom'     => 25,
        ];
    }

    protected function isAbsolutePath(string $path): bool
    {
        return str_starts_with($path, '/') || preg_match('/^[A-Za-z]:\\\\/', $path) === 1;
    }

    protected function renderView(string $view, array $data = []): string
    {
        if (!View::exists($view)) {
            throw new \InvalidArgumentException("View [{$view}] does not exist.");
        }

        return View::make($view, $data)->render();
    }

    /**
     * Add stylesheet
     *
     * @param string|null $path Path to stylesheet
     *
     * @return void
     */
    public function stylesheet(?string $path = null): self
    {
        $path = $path
            ? ($this->isAbsolutePath($path) ? $path : resource_path($path))
            : __DIR__ . '/../css/styles.css';

        if (!is_file($path)) {
            throw new \InvalidArgumentException("Stylesheet [{$path}] does not exist.");
        }

        $stylesheet = file_get_contents($path);
        if ($stylesheet === false) {
            throw new \RuntimeException("Unable to read stylesheet [{$path}].");
        }

        $this->pdf->WriteHTML(
            $stylesheet,
            \Mpdf\HTMLParserMode::HEADER_CSS
        );

        return $this;
    }

    /**
     * Set Header (view)
     *
     * @param string     $header Path to header view.
     * @param array|null $data   View parameters [Optional]
     *
     * @return void
     */
    public function header(
        string $header,
        ?array $data = []
    ): self {
        $view = $this->renderView($header, $data ?? []);
        $this->pdf->setHtmlHeader($view);

        return $this;
    }

    /**
     * Set Footer (view)
     *
     * @param string     $footer Path to footer resource.
     * @param array|null $data   Parameters [Optional]
     *
     * @return void
     */
    public function footer(
        string $footer,
        ?array $data = []
    ): self {
        $view = $this->renderView($footer, $data ?? []);
        $this->pdf->setHtmlFooter($view);

        return $this;
    }

    /**
     * Add View (HTML)
     *
     * @param string     $view Path to view resource.
     * @param array|null $data Parameters
     *
     * @return void
     */
    public function view(
        string $view,
        ?array $data = []
    ): self {
        $view = $this->renderView($view, $data ?? []);
        $this->pdf->WriteHTML($view, \Mpdf\HTMLParserMode::HTML_BODY);

        return $this;
    }

    /**
     * Service Initialization
     *
     * @param array|null  $config     Configuration
     * @param string|null $stylesheet Path to stylesheet
     * @param string|null $header     Path to header resource (view).
     * @param string|null $footer     Path to footer resource (view).
     *
     * @return void
     */
    public function init(
        ?array $config = [],
        ?string $stylesheet = null,
        ?string $header = null,
        ?string $footer = null,
    ): self {
        $this->conf = array_merge($this->defaultConfig(), $config ?? []);
        $this->pdf = new Mpdf($this->conf);

        $this->stylesheet($stylesheet);

        if ($header) {
            $this->header($header);
        }

        if ($footer) {
            $this->footer($footer);
        }

        return $this;
    }

    /**
     * Add a logo
     *
     * It will be available as var:logo
     *
     * @param string $path Path to logo
     *
     * @return void
     */
    public function logo(string $path): self
    {
        $path = $this->isAbsolutePath($path) ? $path : public_path($path);
        if (!is_file($path)) {
            throw new \InvalidArgumentException("Logo [{$path}] does not exist.");
        }

        $imageData = file_get_contents($path);
        if ($imageData === false) {
            throw new \RuntimeException("Unable to read logo [{$path}].");
        }

        $this->pdf->imageVars['logo'] = $imageData;

        return $this;
    }

    /**
     * Sign document using View
     *
     * @param string     $signature The View with your awesome signature.
     * @param array|null $data      View parameters.
     * @param integer    $x         Flush right [mm]
     * @param integer    $y         Vertical alignment [mm]
     * @param integer    $width     Container width [mm]
     *
     * @return void
     */
    public function sign(
        string $signature,
        ?array $data       = [],
        int $x             = 0,
        int $y             = 200,
        int $width         = 50
    ): self {
        $this->pdf->SetXY($x, $y);
        $x = round(3.7795275591 * $x);
        $width = round(3.7795275591 * $width);
        $signature = $this->renderView($signature, $data ?? []);
        $wrapper = <<<EOF
            <div style="margin-left: $x; width: $width; font-family: monospace; font-size: 10px;">
            $signature
            </div>
        EOF;
        $this->pdf->WriteHTML($wrapper, \Mpdf\HTMLParserMode::HTML_BODY);

        return $this;
    }

    /**
     * Load and sign existing PDF.
     *
     * @param string       $signature
     * @param string       $path
     * @param integer|null $x
     * @param integer|null $y
     * @param integer|null $width
     *
     * @return void
     */
    public function signFile(
        string $signature,
        string $path,
        ?array $data = [],
        ?int $x = 0,
        ?int $y = 200,
        ?int $width = 75
    ): self {
        if (!is_file($path)) {
            throw new \InvalidArgumentException("PDF file [{$path}] does not exist.");
        }

        $this->pdf->setSourceFile($path);
        $tplIdx = $this->pdf->importPage(1);
        $this->pdf->useTemplate($tplIdx, 0, 0, 200);
        $this->sign($signature, data: $data, y: $y, x: $x, width: $width);

        return $this;
    }

    /**
     * Get base PDF.
     *
     * @return Mpdf/Mpdf
     */
    public function get(): Mpdf
    {
        return $this->pdf;
    }

    /**
     * Output (stream)
     *
     * @param string|null $filename File name
     *
     * @return void
     */
    public function output(?string $filename = 'document.pdf'): string
    {
        return $this->pdf->output(
            $filename,
            Destination::INLINE
        );
    }

    /**
     * Return the generated PDF as a raw string.
     */
    public function string(): string
    {
        return $this->pdf->output('', Destination::STRING_RETURN);
    }

    /**
     * Output PDF as download.
     */
    public function download(?string $filename = 'document.pdf'): string
    {
        return $this->pdf->output($filename, Destination::DOWNLOAD);
    }

    /**
     * Save PDF to filesystem
     *
     * @param string|null $filename File path.
     *
     * @return void
     */
    public function save(?string $filename = 'document.pdf'): string
    {
        return $this->pdf->output(
            $filename,
            Destination::FILE,
        );
    }

    /**
     * Store to disk using Laravel's Storage Facade
     *
     * @param string      $filename File name (ex. abc.pdf).
     * @param string|null $disk     Disk where to store the file.
     *
     * @return void
     */
    public function storeAs(
        ?string $filename = 'document.pdf',
        ?string $disk = 'public'
    ): bool {
        return Storage::disk($disk)->put(
            $filename,
            $this->string()
        );
    }
}
