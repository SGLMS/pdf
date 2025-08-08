<?php

namespace Sglms\Pdf;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Traits\Macroable;
use Mpdf\Mpdf;

class PdfService
{

    use Macroable;

    protected $pdf;
    protected $conf;

    public function __construct(
        ?array $conf      = [],
        ?string $title    = null,
        ?string $subtitle = null,
        ?string $view     = null,
        ?array $params    = []
    ) {
        $defaultConf = [
            'format'            => 'Letter',
            'default_font'      => 'sans',
            'default_font_size' => 10,
            'margin_top'        => 25,
            'margin_left'       => 25,
            'margin_right'      => 25,
            'margin_bottom'     => 25,
        ];

        $this->conf = $conf;

        $config    = array_merge($defaultConf, $this->conf);
        $this->pdf = new Mpdf($config);
        return $this->pdf;
    }

    /**
     * Add stylesheet
     *
     * @param string|null $path
     *
     * @return void
     */
    public function stylesheet(?string $path = null)
    {
        $path = $path ? resource_path() . $path : __DIR__ . '/../css/styles.css';
        $stylesheet = file_get_contents($path);
        $this->pdf->WriteHTML(
            $stylesheet,
            \Mpdf\HTMLParserMode::HEADER_CSS
        );
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
    ) {
        try {
            $view = View::make($header, $data)->render();
        } catch (\Throwable $th) {
            $view = __("Invalid View!");
        }
        $this->pdf->setHtmlHeader($view);
        return $this;
    }

    public function footer(
        ?string $footer = null,
        ?array $data = []
    ) {
        try {
            $view = View::make($footer, $data)->render();
        } catch (\Throwable $th) {
            $view = __("Invalid View!");
        }
        $this->pdf->setHtmlFooter($view);
        return $this;
    }

    public function view(
        ?string $view = null,
        ?array $data = []
    ) {
        $view = View::make($view, $data)->render();
        $this->pdf->WriteHTML($view, \Mpdf\HTMLParserMode::HTML_BODY);
        return $this;
    }

    public function init(
        ?array $config = [],
        ?string $stylesheet = null,
        ?string $header = null,
        ?string $footer = null,
    ) {
        $this->pdf  = (new self($config))->get();
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
     * @param string $path
     * @return void
     */
    public function logo(string $path)
    {
        $imageData = file_get_contents(public_path() . '/' . $path);
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
    ) {
        $this->pdf->SetXY($x, $y);
        $x = round(3.7795275591 * $x);
        $width = round(3.7795275591 * $width);
        $signature = View::make($signature, $data)->render();
        $wrapper = <<<EOF
            <div style="margin-left: $x; width: $width; font-family: monospace; font-size: 10px;">
            $signature
            </div>
        EOF;
        $this->pdf->WriteHTML($wrapper, \Mpdf\HTMLParserMode::HTML_BODY);
        return $this;
    }

    public function signFile(
        string $signature,
        string $path,
        ?int $x = 0,
        ?int $y = 200,
        ?int $width = 75
    ) {
        /* dump($path, $signature); */
        /* dd($this->pdf); */
        $this->pdf->setSourceFile($path);
        $tplIdx = $this->pdf->importPage(1);
        $this->pdf->useTemplate($tplIdx, 0, 0, 200);
        $this->sign($signature, y: $y, x: $x, width: $width);
    }

    public function get()
    {
        return $this->pdf;
    }

    public function output(?string $filename = 'pdf')
    {
        return $this->pdf->output($filename . '.pdf', \Mpdf\Output\Destination::INLINE);
    }

    public function save(?string $filename = 'pdf')
    {
        return $this->pdf->output($filename . '.pdf', \Mpdf\Output\Destination::FILE);
    }
}
