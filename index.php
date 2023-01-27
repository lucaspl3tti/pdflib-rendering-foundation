<?php

class ExampleRenderingPdfGenerator
{
    private const DEFAULT_FONTSIZE = 10;

    private string $searchPath;
    private array $data;
    private string $templateMain;

    private int $pageWidth;
    private int $pageHeight;

    private int $elementStartLeft;
    private int $elementStartHalf;
    private int $elementEndRight;

    private int $y;
    private int $graphicsY;
    private int $descriptionY;
    private string $colorBlack;
    private string $colorWhite;
    private int $defaultFontsize;

    private array $pageCount;
    private int $currentPageNo;

    private $pdf;
    private int $fontRegular;
    private int $fontBold;
    private int $fontItalic;

    public function getPdfBuffer()
    {
        /* ---------------- Declaration of variables ---------------- */
        // Searchpaths for assets (images, fonts, usw.)
        $this->searchPath = 'assets';

        /*
         * In order for the PDF data to be generated in the RAM an empty file name is passed as the first parameter
         * to "$this->pdf->begin_document()"
         */
        $outfile = '';

        // Asset Declaration
        $this->data = include 'testData.php';
        $config = [
            'fonts' => [
                'ExamplePdf' => [
                    'regular' => 'Regular',
                    'bold' => 'Bold',
                    'italic' => 'Italic',
                ],
            ],
        ];

        $this->templateMain = $this->data['templates']['templateMain'];

        /* ---------- Declaration of PDF options */
        $this->pageWidth = 595;
        $this->pageHeight = 842;

        // Position where elements begin or end
        $this->elementStartLeft = 40;
        $this->elementStartHalf = $this->pageWidth / 2;
        $this->elementEndRight = $this->pageWidth - $this->elementStartLeft;

        // Different height values
        $this->graphicsY = 0;
        $this->descriptionY = 0;

        // Colors
        $this->colorBlack = '{rgb 0 0 0}';
        $this->colorWhite = '{rgb 1 1 1}';

        // Fonts
        $fontExamplePdfRegular = 0;
        $fontExamplePdfBold = 0;
        $fontExamplePdfItalic = 0;

        $this->defaultFontsize = self::DEFAULT_FONTSIZE;

        // Start Coordinate for the pdf
        $this->y = 755;

        // Array for page count
        $this->pageCount = array('1');

        /* ---------------- Generate the PDF-File ---------------- */
        // Generate the \PDFlib Object
        $this->pdf = new \PDFlib();

        // Set PDF options
        $this->setOptions();

        // Set PDF Meta Data
        $this->setMetaData();

        /*
         * Embed fonts
         *
         * fontname: Path to the file based on the searchpath
         */
        foreach ($config['fonts'] as $fontName => $fontConfig) {
            foreach ($fontConfig as $fontStyle => $fontPath) {
                ${
                    'font' . ucfirst($fontName) .
                    ucfirst($fontStyle)
                } = $this->pdf->load_font($fontPath, 'unicode', 'embedding');
            }
        }

        // set global font variables
        $this->fontRegular = $fontExamplePdfRegular;
        $this->fontBold = $fontExamplePdfBold;
        $this->fontItalic = $fontExamplePdfItalic;

        // Filename: If empty, the PDF is created in the working memory and must be fetched with get_buffer.
        if ($this->pdf->begin_document($outfile, '') == 0) {
            throw new Exception('Error: ' . $this->pdf->get_errmsg());
        }

        #### Start generating the pdf document ####
        /*
         * Add new page to the document and specify various options.
         *
         * This function sets all text, graphic and color parameters to their default values and creates a coordinate
         * system according to the topdown option.
         *
         * This function starts the page scope and must be closed with a call of end_page_ext().
         */
        $this->pdf->begin_page_ext($this->pageWidth, $this->pageHeight, '');
        $this->currentPageNo = 1;

        $this->loadGraphics();
        $this->createTextParagraph();
        $this->createTable();
        $this->loadImages();

        $this->generateNewPage();

        $this->loadImages();
        $this->createTextParagraph();

        ##### Place Pagination on all pages #####
        $this->generatePagination();

        /*
         * Closes the generated pdf document and applies various options
         *
         * This function ends the generated PDF document, releases all corresponding resources and closes the PDF
         * document that was opened with begin_document.
         * This function must be called when the client has finished generating the pages, regardless of which method
         * the PDF document was opened with.
         *
         * This function must always be called in combination with begin_document() or begin_document_callback()
         */
        $this->pdf->end_document('');

        /*
         * Get the content from the PDF output buffer.
         *
         * return: string:  - binary PDF data
         *
         * The return value must be used by the client before another PDFlib function can be called.
         */
        return $this->pdf->get_buffer();
    }

    /* ---------------- PDFlib Functions ---------------- */
    /* ---------- Function to set the PDF options, the meta data and the template */
    private function setOptions()
    {
        // Set path in which PDFlib should search for asset files
        $this->pdf->set_option('searchpath={' . $this->searchPath . '}');

        /*
         * Controls Error Handeling
         *
         * exception = Document can not be used in case of an error
         * return = Returns error code 0 and makes internal troubleshooting possible
         */
        $this->pdf->set_option('errorpolicy=return');

        // Makes the application Unicode compatible
        $this->pdf->set_option('stringformat=utf8');
    }

    private function setMetaData()
    {
        $this->pdf->set_info('Subject', $this->data['documentInfo'][0]);
        $this->pdf->set_info('Title', $this->data['documentInfo'][1]);
        $this->pdf->set_info('Creator', $this->data['documentInfo'][2]);
    }

    private function createTemplateOnPage(string $filename, int $pageNumber)
    {
        /*
         * Opens a PDF and prepares it for usage.
         *
         * filename: Name of the file based on the searchpath.
         *
         * return: PDI document handle
         */
        $doc = $this->pdf->open_pdi_document($filename, '');
        if ($doc == 0) {
            throw new Exception('Error: ' . $this->pdf->get_errmsg());
        }

        /*
         * Prepares a page for usage
         *
         * return: PDI page handle
         * The handle can only be used until the end of the closing document scope
         */
        $page = $this->pdf->open_pdi_page($doc, $pageNumber, '');
        if ($page == 0) {
            throw new Exception('Error: ' . $this->pdf->get_errmsg());
        }

        /*
         * Places an imported PDF page on the output page with various options
         *
         * This function is similar to fit_image but works with an imported PDF.
         */
        $this->pdf->fit_pdi_page($page, 0, 0, 'adjustpage');

        // Closes the page handle and releases the resources.
        $this->pdf->close_pdi_page($page);
    }

    private function createHeaderTemplate()
    {
        ## Start page template ##
        $pageTemplate = $this->pdf->begin_template_ext($this->pageWidth, $this->pageHeight, '');

        // Place Heading and Subtitle on the page
        $this->createPdfHeadline();

        ## Finish the template ##
        $this->pdf->end_template_ext(0, 0);

        ## Place the template on the page, just like using an image ##
        $this->pdf->fit_image($pageTemplate, 0.0, 0.0, '');
    }

    private function generateNewPage()
    {
        // Suspend current page
        $this->pdf->suspend_page('');
        $this->pdf->begin_page_ext($this->pageWidth, $this->pageHeight, '');
        ++$this->currentPageNo;

        // Set height to default startpoint
        $this->y = 810;

        // Push new page to array
        array_push($this->pageCount, 'pdfWeberTechnicalSheet Create NewPage');
    }

    private function generatePagination()
    {
        ## Optlist for pagination styling ##
        $optlistPagination = 'font=' . $this->fontItalic .
            ' fontsize=8' .
            ' fillcolor=black' .    // font color
            ' wordspacing=0.5';

        ## Variable declaration of page count ##
        // Maximum page count
        $paginationMax = count($this->pageCount);
        $paginationCurrent = 1;

        ## Place Pagination on all pages ##
        // Suspend current page
        $this->pdf->suspend_page('');

        // resume page number 1
        $this->pdf->resume_page('pagenumber ' . $paginationCurrent);

        $this->createTemplateOnPage($this->templateMain, $paginationCurrent);

        // Place header template on new page
        $this->createHeaderTemplate();

        // Place Pagination on Page 1
        $this->pdf->fit_textline(
            'Seite: ' . $paginationCurrent . '/' . $paginationMax, $this->elementEndRight - 30, 55, $optlistPagination
        );

        /*
         * Complete a page and apply relevant options
         *
         * The options in this function override those in begin_page_ext ()
         *
         * This function exits the page scope and must be opened againg with start_page_ext()
         */
        $this->pdf->end_page_ext('');

        /* If maximum page count is higher than 1 iterate through every page after the first one
         * as long as $i is less then the maximum page count
         */
        if ($paginationMax > 1) {
            for ($i = 1; $i < $paginationMax; $i++) {
                $paginationCurrent++; // get the current page number

                ## Resume the page ##
                $this->pdf->resume_page('pagenumber ' . $paginationCurrent);

                $this->createTemplateOnPage($this->templateMain, 1);

                // Place header template on new page
                $this->createHeaderTemplate();

                // Place Pagination on the Page
                $this->pdf->fit_textline(
                    'Seite: ' . $paginationCurrent . '/' . $paginationMax, $this->elementEndRight - 30, 55,
                    $optlistPagination
                );

                /*
                * Complete a page and apply relevant options
                *
                * The options in this function override those in begin_page_ext ()
                *
                * This function exits the page scope and must be opened againg with start_page_ext()
                */
                $this->pdf->end_page_ext('');
            }
        }
    }

    /* ---------------- Content Functions ---------------- */
    private function replaceHtml(?string $string, int $normalFontsize = self::DEFAULT_FONTSIZE)
    {
        if (!empty($string)) {
            $this->replaceHtmlLists($string);

            $string = $this->removeHtmlTagAttributes($string);

            $searchForMain = [
                1 => '</p><p>',
                2 => '<p> </p>',
                3 => '<p>',
                4 => '<strong>',
                5 => '</strong>',
                6 => '<sup>',
                7 => '</sup>',
                8 => '<sub>',
                9 => '</sub>',
                10 => '<i>',
                11 => '</i>',
                12 => '<em>',
                13 => '</em>',
                14 => '<br/>',
                15 => '<br>',
                16 => '<u>',
                17 => '</u>',
                18 => '<s>',
                19 => '</s>',
                20 => '</p><ul>',
                21 => '</p><ol>',
                22 => '<ol>',
                23 => '</ol>',
                24 => '<ul>',
                25 => '</ul>',
                26 => '<span>',
                27 => '</span>',
                28 => '<br />',
                29 => "\t",
                30 => "\r\n",
                31 => "\r",
                32 => '</p>',
                33 => '<li>',
                34 => '</li>',
                35 => '</div><div>',
                36 => '<div> </div>',
                37 => '<div>',
                38 => '</div>',
                39 => '<table>',
                40 => '</table>',
                41 => '<tbody>',
                42 => '</tbody>',
                43 => '<tr>',
                44 => '</tr>',
                45 => '<td>',
                46 => '</td>',
            ];

            $replaceWithMain = [
                1 => "\n",
                2 => "\n",
                3 => '',
                4 => '<font=' . $this->fontBold . '>',
                5 => '<font=' . $this->fontRegular . '>',
                6 => '<textrise=60% fontsize=6>',
                7 => '<textrise=0 fontsize=' . $normalFontsize . '>',
                8 => '<textrise=-60% fontsize=6>',
                9 => '<textrise=0 fontsize=9>',
                10 => '<italicangle=-12>',
                11 => '<italicangle=0>',
                12 => '<italicangle=-12>',
                13 => '<italicangle=0>',
                14 => "\n",
                15 => "\n",
                16 => '<underline=true underlinewidth=7% underlineposition=-20%>',
                17 => '<underline=false>',
                18 => '<strikeout=true>',
                19 => '<strikeout=false>',
                20 => "\n",
                21 => "\n",
                22 => '',
                23 => '<leftindent=0>',
                24 => '',
                25 => '<leftindent=0>',
                26 => '',
                27 => '',
                28 => "\n",
                29 => '',
                30 => '',
                31 => '',
                32 => '',
                33 => '',
                34 => '',
                35 => "\n",
                36 => "\n",
                37 => '',
                38 => "\n",
                39 => '',
                40 => "\n",
                41 => '',
                42 => "\n",
                43 => '',
                44 => "\n",
                45 => '',
                46 => "\n",
            ];

            // Go over every item in the list so the corresponding tag is also replaced when it's written in caps only
            foreach ($searchForMain as $key => $searchValue) {
                $string = str_replace(strtoupper($searchValue), $replaceWithMain[$key], $string);
            }

            // replace all normal occurences of the html tags in the list
            $string = str_replace($searchForMain, $replaceWithMain, $string);
        }

        return $string;
    }

    private function removeHtmlTagAttributes(?string $string)
    {
        if (!empty($string)) {
            // remove any inline styles from html tags
            return preg_replace('/<([a-z][a-z0-9]*)[^>]*?(\/?)>/si', '<$1$2>', $string);
        }
    }

    private function replaceHtmlLists(?string $string)
    {
        while (strpos($string, '<ol>') !== false || strpos($string, '<ul>') !== false) {
            $explodeArray = explode('<ol>', $string);
            if (empty($explodeArray[0])) {
                array_shift($explodeArray);
            }

            foreach ($explodeArray as &$listArrayItem) {
                if (
                    strpos($listArrayItem, '</ul>') !== false
                    && strpos($listArrayItem, '</ol>') === false
                ) {
                    $searchForUnorderedEls = [
                        1 => '<li>',
                        2 => '</li>',
                    ];

                    $replaceWithUnorderedOptlists = [
                        1 => '<leftindent=0>&bull;<leftindent=10> ',
                        2 => "\n",
                    ];

                    $listArrayItem = str_replace(
                        $searchForUnorderedEls,
                        $replaceWithUnorderedOptlists,
                        $listArrayItem
                    );
                } elseif (
                    strpos($listArrayItem, '</ul>') !== false
                    && strpos($listArrayItem, '</ol>') !== false
                ) {
                    $orderedNumber = 0;
                    $explodeArrayOrdered = explode('<ul>', $listArrayItem);

                    foreach ($explodeArrayOrdered as &$orderedListArrayItem) {
                        if (strpos($orderedListArrayItem, '</ol>') !== false) {
                            $explodeOrderedEls = explode("\t", $orderedListArrayItem);
                            if (empty($explodeOrderedEls[0])) {
                                array_shift($explodeOrderedEls);
                            }

                            foreach ($explodeOrderedEls as &$orderedEl) {
                                ++$orderedNumber;

                                $searchForOrderedListItems = [
                                    1 => '<li>',
                                    2 => '</li>',
                                ];

                                $replaceWithOrderedOptlists = [
                                    1 => '<leftindent=0>' . $orderedNumber . '.<leftindent=10>',
                                    2 => "\n",
                                ];

                                $orderedEl = str_replace(
                                    $searchForOrderedListItems,
                                    $replaceWithOrderedOptlists,
                                    $orderedEl
                                );
                            }

                            $orderedListArrayItem = implode($explodeOrderedEls);
                        } elseif (strpos($orderedListArrayItem, '</ul>') !== false) {
                            $searchForUnorderedEls = [
                                1 => '<li>',
                                2 => '</li>',
                            ];

                            $replaceWithUnorderedOptlists = [
                                1 => '<leftindent=0>&bull;<leftindent=10> ',
                                2 => "\n",
                            ];

                            $orderedListArrayItem = str_replace(
                                $searchForUnorderedEls,
                                $replaceWithUnorderedOptlists,
                                $orderedListArrayItem
                            );
                        }
                    }
                    $listArrayItem = implode($explodeArrayOrdered);
                } elseif (
                    strpos($listArrayItem, '</ul>') == false
                    && strpos($listArrayItem, '</ol>') !== false
                ) {
                    $orderedNumber = 0;
                    $explodeOrderedEls = explode("\t", $listArrayItem);
                    if (empty($explodeOrderedEls[0])) {
                        array_shift($explodeOrderedEls);
                    }

                    foreach ($explodeOrderedEls as &$orderedEl) {
                        ++$orderedNumber;

                        $searchForOrderedListItems = [
                            1 => '<li>',
                            2 => '</li>',
                        ];

                        $replaceWithOrderedOptlists = [
                            1 => '<leftindent=0>' . $orderedNumber . '.<leftindent=10>',
                            2 => "\n",
                        ];

                        $orderedEl = str_replace(
                            $searchForOrderedListItems,
                            $replaceWithOrderedOptlists,
                            $orderedEl
                        );
                    }

                    $listArrayItem = implode($explodeOrderedEls);
                }
            }
            $string = implode($explodeArray);

            return $string;
        }
    }

    private function placePartingLine()
    {
        // Get height value
        $this->y = $this->y - 5;

        // Define width of the parting line
        $this->pdf->setlinewidth(1);

        // Define stroke color
        $this->pdf->setcolor('stroke', 'rgb', 0.0, 0.0, 0.0, 0.0);

        // Define fill color
        $this->pdf->setcolor('fill', 'rgb', 0.0, 0.0, 0.0, 0.0);

        // Set starting point of parting line
        $this->pdf->moveto($this->elementStartLeft, $this->y);

        // Draw parting line from starting point to end point
        $this->pdf->lineto($this->elementEndRight, $this->y);

        $this->pdf->stroke();

        // Get new height value
        $this->y = $this->y - 5;
    }

    /* ---------- Generate Page Headline and create a header template with them */
    private function createPdfHeadline()
    {
        $leftX = $this->elementStartLeft;
        $leftY = 815;
        $rightX = $this->elementEndRight;
        $rightY = $leftY - 20;

        // Define text input
        $headline = $this->data['headline'];

        $optlistHeadline = 'font=' . $this->fontBold . ' fontsize=16 fillcolor=white wordspacing=0';

        $tf = $this->pdf->add_textflow(0, $headline, $optlistHeadline);
        if ($tf == 0) {
            throw new Exception('Error: ' . $this->pdf->get_errmsg());
        }

        // Output textflow on page
        $result = $this->pdf->fit_textflow($tf, $leftX, $leftY, $rightX, $rightY, '');

        // If the text doesn't fit into the fitbox throw an exception
        if ($result == '_boxfull') {
            throw new Exception('Text of headline does not fit into the fitbox');
        }
    }

    private function loadGraphics()
    {
        $svgImages = $this->data['graphics'];

        // start coordinates of the images
        $imageY = $this->y - 50;
        $imageX = $this->elementEndRight - 45;

        // image box delcaration
        $boxwidth = 45;
        $boxheight = 45;

        // loop until all images are placed
        foreach ($svgImages as $svg) {
            // load svg graphic
            $graphics = $this->pdf->load_graphics('auto', $svg, '');
            if ($graphics == 0) {
                echo('Couldn not load logo image: ' . $this->pdf->get_errmsg());
                exit(1);
            }

            // place the image
            $buf = 'boxsize={ ' . $boxwidth . ' ' . $boxheight . '} position={center} fitmethod=meet';
            $this->pdf->fit_graphics($graphics, $imageX, $imageY, $buf);

            $imageY = $imageY - 55;
        }

        $this->graphicsY = $imageY + 55;
    }

    private function createTextParagraph()
    {
        ##### Variable Declaration #####
        $text = $this->data['paragraph'];

        $left_x = $this->elementStartLeft;
        $left_y = $this->y;
        $right_x = $this->elementEndRight - 100;
        $right_y = 400;

        #### Add textflow ####
        $optlist = 'font=' . $this->fontRegular . ' fontsize=' . $this->defaultFontsize
            . ' fillcolor=black wordspacing=0.5 leading=13';

        $text = $this->replaceHtml($text);

        $tf = $this->pdf->create_textflow($text, $optlist);
        if ($tf == 0) {
            throw new Exception('Error: ' . $this->pdf->get_errmsg());
        }

        // Output textflow on page
        $result = $this->pdf->fit_textflow($tf, $left_x, $left_y, $right_x, $right_y, '');

        // If the text doesn't fit into the fitbox throw an exception
        if ($result == '_boxfull') {
            throw new Exception('Text of Paragraph does not fit into the fitbox');
        }

        // Get height of the fitbox
        $infoHeight = $this->pdf->info_textflow($tf, 'y2');

        $this->descriptionY = $infoHeight;
    }

    private function createHeading(string $string, int $paddingBottom = 20, bool $renderPartinLine = true)
    {
        if (!empty($string)) {
            $left_x = $this->elementStartLeft;
            $left_y = $this->y;
            $right_x = $this->elementEndRight;
            $right_y = $this->y - 20;

            ## Add textflow ##
            $optlistHeading = 'font=' . $this->fontBold . ' fontsize=12 fillcolor=black wordspacing=0.5 leading=13';

            // create textflow
            $headingTf = $this->pdf->add_textflow(0, $string, $optlistHeading);
            if ($headingTf == 0) {
                throw new Exception('Error: ' . $this->pdf->get_errmsg());
            }

            // Output textflow on page
            $resultHeading = $this->pdf->fit_textflow($headingTf, $left_x, $left_y, $right_x, $right_y, '');

            // If the text doesn't fit into the fitbox throw an exception
            if ($resultHeading == '_boxfull') {
                throw new Exception('The Heading "' . $string . '" does not fit into the fitbox');
            }

            if ($renderPartinLine) {
                ##### Place Parting Line #####
                $this->y = $this->y - 15;
                $this->placePartingLine();
            }

            $this->y = $this->y - $paddingBottom;
        }
    }

    private function createTable()
    {
        ## get new height value for following elements ##
        if ($this->descriptionY < $this->graphicsY) {
            $this->y = $this->descriptionY - 20;
        } else {
            $this->y = $this->graphicsY - 20;
        }

        $tableHeading = $this->data['table']['tableHeading'];
        $this->createHeading($tableHeading, 20, false);

        ### Variable Declaration ###
        $tbl = 0;
        $row = 0;
        $col1 = 1;
        $col2 = 2;

        // Coordinates for productProfile Table
        $left_x = $this->elementStartLeft;
        $left_y = $this->y;
        $right_x = $this->elementEndRight;
        $right_y = 310;

        // Define list item variables
        $tableContent = $this->data['table']['tableContent'];

        $optlistTableTf = 'font=' . $this->fontRegular . ' fontsize=10 fillcolor=black wordspacing=0' . ' leading=13';

        ### create table ###
        // add cell for every item in $tableContent
        foreach ($tableContent as $key => $value) {
            $row++;

            ## Add $key cell ##
            // Add new textflow
            $tf = $this->pdf->add_textflow(0, $key, $optlistTableTf);
            if ($tf == 0) {
                throw new Exception('Error: ' . $this->pdf->get_errmsg());
            }

            // Add new table cell which contains the textflow
            $optlistTableCellLeft = 'colwidth=50% margintop=4 marginbottom=4 marginleft=4 marginright=4'
                . ' fittextflow={verticalalign=top} textflow=' . $tf;

            $tbl = $this->pdf->add_table_cell($tbl, $col1, $row, '', $optlistTableCellLeft);
            if ($tbl == 0) {
                throw new Exception('Error: ' . $this->pdf->get_errmsg());
            }

            ## Add $value cell ##
            // add new textflow
            $tf = $this->pdf->add_textflow(0, $value, $optlistTableTf);
            if ($tf == 0) {
                throw new Exception('Error: ' . $this->pdf->get_errmsg());
            }

            // Add new table cell which contains the textflow
            $optlistTableCellRight = 'colwidth=50% margintop=4 marginbottom=4 marginleft=4 marginright=4'
                . ' fittextflow={verticalalign=top} textflow=' . $tf;

            $tbl = $this->pdf->add_table_cell($tbl, $col2, $row, '', $optlistTableCellRight);
            if ($tbl == 0) {
                throw new Exception('Error: ' . $this->pdf->get_errmsg());
            }
        }

        // Output technical Values Table on page
        $optlistTable = 'rowheightdefault=15 '
            . 'stroke={{line=frame linewidth=1} {line=vertother linewidth=1} {line=horother linewidth=1}} ';

        $resultTable = $this->pdf->fit_table($tbl, $left_x, $left_y, $right_x, $right_y, $optlistTable);

        // If the table doesn't fit into the fitbox throw an exception
        if ($resultTable == '_boxfull') {
            throw new Exception('Table does not fit into the fitbox');
        }

        ## get height value of productProfile table ##
        // Get table height
        $resultTableY2 = $this->pdf->info_table($tbl, 'y2');

        // Return new height value
        $this->y = $resultTableY2 - 30;
    }

    private function loadImages() {
        $imageHeading = $this->data['image']['heading'];
        $this->createHeading($imageHeading, 75);

        $imageSource = $this->data['image']['source'];

        // start coordinates of the images
        $offsetTop = $this->currentPageNo === 1 ? 70 : 100;
        $imageY = $this->y - $offsetTop;
        $imageX = $this->elementStartLeft;

        // load the image
        $image = $this->pdf->load_image('auto', $imageSource, '');
        if ($image == 0) {
            echo('Couldn not load logo image: ' . $this->pdf->get_errmsg());
            exit(1);
        }

        // place the image on the page
        $buf = 'scale=0.2 position={left} matchbox={name=image}';
        $this->pdf->fit_image($image, $imageX, $imageY, $buf);

        if ($this->pdf->info_matchbox("image", 1, "exists") == 1 && $this->currentPageNo > 1) {
            $y2 = $this->pdf->info_matchbox("image", 1, "y2");

            $this->y = $y2 - 20;
        }
    }
}

/* ---------------- Call the Class ---------------- */
try {
    $exampleRenderingPdfGenerator = new ExampleRenderingPdfGenerator();
    $pdfBuffer = $exampleRenderingPdfGenerator->getPdfBuffer();
} catch (\Throwable $throwable) {
    throw $throwable;
    exit();
}

header('Content-type: application/pdf');
header('Content-Disposition: inline');

print $pdfBuffer;
