<?php

class ExampleRenderingPdfGenerator
{
    public function getPdfBuffer()
    {
        /* ---------------- Declaration of variables ---------------- */
        // Searchpaths for assets (images, fonts, usw.)
        $searchpath = 'assets';

        /* In order for the PDF data to be generated in the RAM an empty file name is passed as the first parameter
         * to "$p->begin_document()"
         */
        $outfile = '';

        // Asset Declaration
        $arrInput = include 'testData.php';
        $startTemplate = $arrInput['startTemplate'];
        $config = [
            'fonts' => [
                'ExamplePdf' => [
                    'regular' => 'Regular',
                    'bold' => 'Bold',
                    'italic' => 'Italic',
                ],
            ],
        ];

        /* ---------- Declaration of PDF options */
        $pagewidth = 595;
        $pageheight = 842;

        // Position where elements begin or end
        $elementStartLeft = 40;
        $elementStartHalf = $pagewidth / 2;
        $elementEndRight = $pagewidth - $elementStartLeft;

        // Different height values
        $graphicsY = 0;
        $descriptionY = 0;

        // Document and Page Variable for the template
        $doc = 1;
        $page = 1;

        // Fonts
        $fontExamplePdfRegular = 0;
        $fontExamplePdfBold = 0;
        $fontExamplePdfItalic = 0;

        // Start Coordinate for the pdf
        $y = 755;

        // Array for page count
        $pageCount = array(
            '1',
        );

        // Maximum page number
        $paginationMax = 0;

        // Current page number
        $paginationCurrent = 0;

        /* ---------------- Generate the PDF-File ---------------- */
        // Generate the \PDFlib Object
        $p = new \PDFlib();

        // Set PDF options
        $this->setOptions($p, $searchpath);

        // Set PDF Meta Data
        $this->setMetaData($p, $arrInput);

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
                } = $p->load_font($fontPath, 'unicode', 'embedding');
            }
        }

        // Filename: If empty, the PDF is created in the working memory and must be fetched with get_buffer.
        if ($p->begin_document($outfile, '') == 0) {
            throw new Exception('Error: ' . $p->get_errmsg());
        }

        /* ----- Start generating the pdf document ---- */
        /*
         * Add new page to the document and specify various options.
         *
         * This function sets all text, graphic and color parameters to their default values and creates a coordinate
         * system according to the topdown option.
         *
         * This function starts the page scope and must be closed with a call of end_page_ext().
         */
        $p->begin_page_ext($pagewidth, $pageheight, '');


        $this->loadGraphics($p, $elementEndRight, $y, $graphicsY, $arrInput);

        $this->createTextParagraph(
            $p,
            $elementStartLeft,
            $elementEndRight,
            $y, $descriptionY,
            $fontExamplePdfRegular,
            $fontExamplePdfBold,
            $pagewidth,
            $pageheight,
            $arrInput,
            $pageCount
        );

        $this->createTable(
            $p,
            $y,
            $elementStartLeft,
            $elementEndRight,
            $fontExamplePdfRegular,
            $fontExamplePdfBold,
            $arrInput,
            $graphicsY,
            $descriptionY,
            $pageCount,
            $pagewidth,
            $pageheight
        );

        $this->loadImages(
            $p,
            $elementStartLeft,
            $elementEndRight,
            $fontExamplePdfBold,
            $y,
            $arrInput,
            $pageheight,
            $pagewidth,
            $pageCount
        );

        ##### Place Pagination on all pages #####
        $this->generatePagination(
            $p,
            $pageCount,
            $paginationMax,
            $paginationCurrent,
            $elementStartLeft,
            $elementEndRight,
            $fontExamplePdfBold,
            $fontExamplePdfItalic,
            $startTemplate,
            $pagewidth,
            $pageheight,
            $arrInput
        );

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
        $p->end_document('');

        /*
         * Get the content from the PDF output buffer.
         *
         * return: string:  - binary PDF data
         *
         * The return value must be used by the client before another PDFlib function can be called.
         */
        return $p->get_buffer();
    }

    /* ---------------- PDFlib Functions ---------------- */
    /* ---------- Function to set the PDF options, the meta data and the template */
    /**
     * Set the pdf options
     *
     * @param \PDFlib $p            PDFLib object
     * @param string $searchpath    Path in which PDFlib searches for assets
     * @return void
     */
    private function setOptions(\PDFlib $p, string $searchpath)
    {
        // Set path in which PDFlib should search for asset files
        $p->set_option('searchpath={' . $searchpath . '}');

        /*
         * Controls Error Handeling
         *
         * exception = Document can not be used in case of an error
         * return = Returns error code 0 and makes internal troubleshooting possible
         */
        $p->set_option('errorpolicy=return');

        // Makes the application Unicode compatible
        $p->set_option('stringformat=utf8');
    }

    /**
     * Set meta data of the document
     *
     * @param \PDFlib $p    PDFLib object
     * @param array $arr    Array with the data (demodata)
     * @return void
     */
    private function setMetaData(\PDFlib $p, array $arr)
    {
        $p->set_info('Subject', $arr['documentInfo'][0]);
        $p->set_info('Title', $arr['documentInfo'][1]);
        $p->set_info('Creator', $arr['documentInfo'][2]);
    }

    /**
     * Create a template and place it under the page
     *
     * @param \PDFlib $p        PDFLib object
     * @param string $filename  Filename of the template
     * @param int $pageNumber   Which page should be imported
     * @return void
     */
    private function createTemplateOnPage(\PDFlib $p, string $filename, int $pageNumber)
    {
        /*
         * Opens a PDF and prepares it for usage.
         *
         * filename: Name of the file based on the searchpath.
         *
         * return: PDI document handle
         */
        $doc = $p->open_pdi_document($filename, '');
        if ($doc == 0) {
            throw new Exception('Error: ' . $p->get_errmsg());
        }

        /*
         * Prepares a page for usage
         *
         * return: PDI page handle
         * The handle can only be used until the end of the closing document scope
         */
        $page = $p->open_pdi_page($doc, $pageNumber, '');
        if ($page == 0) {
            throw new Exception('Error: ' . $p->get_errmsg());
        }

        /*
         * Places an imported PDF page on the output page with various options
         *
         * This function is similar to fit_image but works with an imported PDF.
         */
        $p->fit_pdi_page($page, 0, 0, 'adjustpage');

        // Closes the page handle and releases the resources.
        $p->close_pdi_page($page);
    }

    /* ---------- Functions to generate a new page and create functions to easy implement content */
    private function generateNewPage(
        \PDFlib $p,
        int $pagewidth,
        int $pageheight,
        int &$y,
        array &$pageCount
    ) {
        // Suspend current page
        $p->suspend_page('');
        $p->begin_page_ext($pagewidth, $pageheight, '');

        // Set height to default startpoint
        $y = 755;

        // Push new page to array
        array_push($pageCount, 'NewPage');
    }

    private function generatePagination(
        pdflib $p,
        array $pageCount,
        int $paginationMax,
        int $paginationCurrent,
        int $elementStartLeft,
        int $elementEndRight,
        int $fontExamplePdfBold,
        int $fontExamplePdfItalic,
        string $startTemplate,
        int $pagewidth,
        int $pageheight,
        array $arrInput
    ) {
        ## Optlist for pagination styling ##
        $optlistPagination = 'font=' . $fontExamplePdfItalic . ' fontsize=8 fillcolor=black wordspacing=0.5';

        ## Variable declaration of page count ##
        // Maximum page count
        $paginationMax = count($pageCount);

        // Get the current page number, here it equals 1 because default is 0
        $paginationCurrent++;

        ## Place Pagination on all pages ##
        // Suspend current page
        $p->suspend_page('');

        // resume page number 1
        $p->resume_page('pagenumber ' . $paginationCurrent);

        $this->createTemplateOnPage($p, $startTemplate, 1);

        // Place header template on new page
        $this->generateHeaderTemplate(
            $p,
            $fontExamplePdfBold,
            $elementStartLeft,
            $elementEndRight,
            $pagewidth,
            $pageheight,
            $arrInput
        );

        // Place Pagination on Page 1
        $p->fit_textline(
            'Page: ' . $paginationCurrent . '/' . $paginationMax, $elementEndRight - 30, 55, $optlistPagination
        );

        $p->end_page_ext('');

        /* If maximum page count is higher than 1 iterate through every page after the first one
         * as long as $i is less then the maximum page count
         */
        if ($paginationMax > 1) {
            for ($i = 1; $i < $paginationMax; $i++) {
                $paginationCurrent++; // get the current page number

                ## Resume the page ##
                $p->resume_page('pagenumber ' . $paginationCurrent);

                $this->createTemplateOnPage($p, $startTemplate, 1);

                // Place header template on new page
                $this->generateHeaderTemplate(
                    $p,
                    $fontExamplePdfBold,
                    $elementStartLeft,
                    $elementEndRight,
                    $pagewidth,
                    $pageheight,
                    $arrInput
                );

                // Place Pagination on the Page
                $p->fit_textline(
                    'Page: ' . $paginationCurrent . '/' . $paginationMax, $elementEndRight - 30, 55, $optlistPagination
                );

                $p->end_page_ext('');
            }
        }
    }

    private function placePartingLine(\PDFlib $p, int $elementStartLeft, int $elementEndRight, int &$y)
    {
        // Get height value
        $y = $y - 5;

        // Define width of the parting line
        $p->setlinewidth(1);

        // Define stroke color
        $p->setcolor('stroke', 'rgb', 0.0, 0.0, 0.0, 0.0);

        // Define fill color
        $p->setcolor('fill', 'rgb', 0.0, 0.0, 0.0, 0.0);

        // Set starting point of parting line
        $p->moveto($elementStartLeft, $y);

        // Draw parting line from starting point to end point
        $p->lineto($elementEndRight, $y);

        $p->stroke();

        // Get new height value
        return $y = $y - 5;
    }

    private function createHeading(
        \PDFlib $p,
        int &$y,
        int $elementStartLeft,
        int $elementEndRight,
        int $fontBold,
        array $arrInput,
        string $heading
    ) {
        ## Variable Declaration ##
        $leftX = $elementStartLeft;
        $leftY = $y - 20;
        $rightX = $elementEndRight;
        $rightY = $y;

        ## Add textflow ##
        $optlistHeading = 'font=' . $fontBold . ' fontsize=12 fillcolor=black wordspacing=0.5 leading=13';

        // create textflow
        $headingTf = $p->add_textflow(0, $heading, $optlistHeading);
        if ($headingTf == 0) {
            throw new Exception('Error: ' . $p->get_errmsg());
        }

        // Output textflow on page
        $resultHeading = $p->fit_textflow(
            $headingTf,
            $leftX,
            $leftY,
            $rightX,
            $rightY,
            ''
        );

        // If the text doesn't fit into the fitbox throw an exception
        if ($resultHeading == '_boxfull') {
            throw new Exception('Text of imageHeading does not fit into the fitbox');
        }

        ##### Place Parting Line #####
        $y = $y - 15;
        $this->placePartingLine($p, $elementStartLeft, $elementEndRight, $y);

        return $y = $y - 5;
    }

    /* ---------- Functions to make HTML-Tags useable with PDFlib */
    private function replaceHtml(?string $string, int $fontRegular, int $fontBold, string $normalFontsize)
    {

        $this->replaceHtmlLists($string, $fontRegular);

        $pCounter = substr_count($string, '<p>');

        if ($pCounter > 1) {
            $array = explode('<p>', $string);
            if (!empty($array)) {
                array_shift($array);
                $string = implode($array);

                // check for html tags and replace them
                $searchForP = [
                    1 => '</p>',
                ];

                $replaceWithBr = [
                    1 => '<br/>',
                ];

                $string = str_replace($searchForP, $replaceWithBr, $string);
            }
        }

        $searchForMain = [
            1 => '<p> </p>',
            2 => '<p>',
            3 => '<strong>',
            4 => '</strong>',
            5 => '<sup>',
            6 => '</sup>',
            7 => '<sub>',
            8 => '</sub>',
            9 => '<i>',
            10 => '</i>',
            11 => '<em>',
            12 => '</em>',
            13 => '<br/>',
            14 => '<br>',
            15 => '<u>',
            16 => '</u>',
            17 => '<s>',
            18 => '</s>',
            19 => '<ol>',
            20 => '</ol>',
            21 => '<ul>',
            22 => '</ul>',
            23 => '<span>',
            24 => '</span>',
            25 => '<br />',
            26 => "\t",
            27 => "\r\n",
            28 => "\r",
            29 => '</p>',
            30 => '<b>',
            31 => '</b>',
        ];

        $replaceWithMain = [
            1 => "\n",
            2 => '',
            3 => '<font=' . $fontBold . '>',
            4 => '<font=' . $fontRegular . '>',
            5 => '<textrise=60% fontsize=6>',
            6 => '<textrise=0 fontsize=' . $normalFontsize . '>',
            7 => '<textrise=-60% fontsize=6>',
            8 => '<textrise=0 fontsize=9>',
            9 => '<italicangle=-12>',
            10 => '<italicangle=0>',
            11 => '<italicangle=-12>',
            12 => '<italicangle=0>',
            13 => "\n",
            14 => "\n",
            15 => '<underline=true underlinewidth=7% underlineposition=-20%>',
            16 => '<underline=false>',
            17 => '<strikeout=true>',
            18 => '<strikeout=false>',
            19 => '',
            20 => '<leftindent=0>',
            21 => '',
            22 => '<leftindent=0>',
            23 => '',
            24 => '',
            25 => "\n",
            26 => '',
            27 => '',
            28 => '',
            29 => '',
            30 => '<font=' . $fontBold . '>',
            31 => '<font=' . $fontRegular . '>',
        ];

        return str_replace($searchForMain, $replaceWithMain, $string);
    }

    private function replaceHtmlLists(string &$string, int $font)
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
                        1 => '<leftindent=0 fontname=Symbol encoding=unicode>&#x2022;<leftindent=10 font='
                            . $font . '>',
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

                            foreach ($explodeOrderedEls as &$OrderedEl) {
                                ++$orderedNumber;

                                $searchForOrderedListItems = [
                                    1 => '<li>',
                                    2 => '</li>',
                                ];

                                $replaceWithOrderedOptlists = [
                                    1 => '<leftindent=0>' . $orderedNumber . '.<leftindent=10>',
                                    2 => "\n",
                                ];

                                $OrderedEl = str_replace(
                                    $searchForOrderedListItems,
                                    $replaceWithOrderedOptlists,
                                    $OrderedEl
                                );
                            }

                            $orderedListArrayItem = implode($explodeOrderedEls);
                        } elseif (strpos($orderedListArrayItem, '</ul>') !== false) {
                            $searchForUnorderedEls = [
                                1 => '<li>',
                                2 => '</li>',
                            ];

                            $replaceWithUnorderedOptlists = [
                                1 => '<leftindent=0 fontname=Symbol encoding=unicode>&#x2022;<leftindent=10 font='
                                    . $font . '>',
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

                    foreach ($explodeOrderedEls as &$OrderedEl) {
                        ++$orderedNumber;

                        $searchForOrderedListItems = [
                            1 => '<li>',
                            2 => '</li>',
                        ];

                        $replaceWithOrderedOptlists = [
                            1 => '<leftindent=0>' . $orderedNumber . '.<leftindent=10>',
                            2 => "\n",
                        ];

                        $OrderedEl = str_replace(
                            $searchForOrderedListItems,
                            $replaceWithOrderedOptlists,
                            $OrderedEl
                        );
                    }

                    $listArrayItem = implode($explodeOrderedEls);
                }
            }
            return $string = implode($explodeArray);
        }
    }

    /* ---------- Create Page Heading and generate a header template with them */
    private function createDocumentHeadline(
        \PDFlib $p,
        int $fontExamplePdfBold,
        int $elementStartLeft,
        int $elementEndRight,
        array $arrInput
    ) {
        $leftX = $elementStartLeft;
        $leftY = 815;
        $rightX = $elementEndRight;
        $rightY = $leftY - 20;

        // Define text input
        $headline = $arrInput['headline'];

        $optlistHeadline = 'font=' . $fontExamplePdfBold . ' fontsize=16 fillcolor=white wordspacing=0';

        $tf = $p->add_textflow(0, $headline, $optlistHeadline);
        if ($tf == 0) {
            throw new Exception('Error: ' . $p->get_errmsg());
        }

        // Output textflow on page
        $result = $p->fit_textflow($tf, $leftX, $leftY, $rightX, $rightY, '');

        // If the text doesn't fit into the fitbox throw an exception
        if ($result == '_boxfull') {
            throw new Exception('Text of headline does not fit into the fitbox');
        }
    }

    private function generateHeaderTemplate(
        \PDFlib $p,
        int $fontExamplePdfBold,
        int $elementStartLeft,
        int $elementEndRight,
        int $pagewidth,
        int $pageheight,
        array $arrInput
    ) {
        ## Start page template ##
        $pageTemplate = $p->begin_template_ext($pagewidth, $pageheight, '');

        // Place Heading and Subtitle on the page
        $this->createDocumentHeadline($p, $fontExamplePdfBold, $elementStartLeft, $elementEndRight, $arrInput);

        ## Finish the template ##
        $p->end_template_ext(0, 0);

        ## Place the template on the page, just like using an image ##
        $p->fit_image($pageTemplate, 0.0, 0.0, '');
    }

    /* ---------- Functions to place the content */
    private function loadGraphics(
        \PDFlib $p,
        int $elementEndRight,
        int $y,
        int &$graphicsY,
        array $arrInput
    ) {
        $svgImages = $arrInput['graphics'];
        $graphicCounter = count($svgImages);

        if ($graphicCounter > 12) {
            throw new Exception('Error: More than 12 images');
        }

        // start coordinates of the images
        $imageY = $y - 50;
        $imageX = $elementEndRight - 45;

        // image box delcaration
        $boxwidth = 45;
        $boxheight = 45;

        // loop until all images are placed
        foreach ($svgImages as $svg) {
            // load svg graphic
            $graphics = $p->load_graphics('auto', $svg, '');
            if ($graphics == 0) {
                throw new Exception('Couldn not load logo image: ' . $p->get_errmsg());
            }

            // place the image
            $buf = 'boxsize={ ' . $boxwidth . ' ' . $boxheight . '} position={center} fitmethod=meet';
            $p->fit_graphics($graphics, $imageX, $imageY, $buf);

            $imageY = $imageY - 55;
        }

        $graphicsY = $imageY + 55;
    }

    private function createTextParagraph(
       \PDFlib $p,
       int $elementStartLeft,
       int $elementEndRight,
       int $y,
       int &$descriptionY,
       int $fontRegular,
       int $fontBold,
       int $pagewidth,
       int $pageheight,
       array $arrInput,
       array &$pageCount
    ) {
        ##### Variable Declaration #####
        $paragraph = $arrInput['paragraph'];

        $leftX = $elementStartLeft;
        $leftY = 100;
        $rightX = $elementEndRight - 100;
        $rightY = $y;

        #### Add textflow ####
        $optlist = 'font=' . $fontRegular . ' fontsize=10 fillcolor=black wordspacing=0.5 leading=13';

        $normalFontsize = '10';
        $paragraph = $this->replaceHtml(
            $paragraph,
            $fontRegular,
            $fontBold,
            $normalFontsize
        );

        $tf = $p->create_textflow($paragraph, $optlist);
        if ($tf == 0) {
            throw new Exception('Error: ' . $p->get_errmsg());
        }

        // Output textflow on page
        $resultParagraph = $p->fit_textflow($tf, $leftX, $leftY, $rightX, $rightY, '');

        // If the text doesn't fit into the fitbox throw an exception
        while ($resultParagraph != '_stop') {
            $this->generateNewPage(
                $p,
                $pagewidth,
                $pageheight,
                $y,
                $pageCount,
            );

            // New height Coordinates for the first page
            $leftY = 100;
            $rightY = 755;

            // Output textflow on new page
            $resultParagraph = $p->fit_textflow($tf, $leftX, $leftY, $rightX, $rightY, '');
        }

        // Get height of the fitbox
        $infoHeight = $p->info_textflow($tf, 'y2');

        $descriptionY = $infoHeight;
    }

    private function createTable(
        \PDFlib $p,
        int &$y,
        int $elementStartLeft,
        int $elementEndRight,
        int $fontRegular,
        int $fontBold,
        array $arrInput,
        int $graphicsY,
        int $descriptionY,
        array &$pageCount,
        int $pagewidth,
        int $pageheight
    ) {
        $tableHeading = $arrInput['table']['tableHeading'];
        $tableContent = $arrInput['table']['tableContent'];

        // count all images
        $svgImages = $arrInput['graphics'];
        $graphicCounter = count($svgImages);

        ## get new height value for element ##
        $pageCounter = count($pageCount);
        if ($pageCounter > 1) {
            $y = $descriptionY - 20;
        } else {
            if ($descriptionY < $graphicsY) {
                $y = $descriptionY - 20;
            } else {
                $y = $graphicsY - 20;
            }
        }

        if ($y < 120 || $graphicCounter > 10) {
            $this->generateNewPage(
                $p,
                $pagewidth,
                $pageheight,
                $y,
                $pageCount,
            );
        }

        $this->createHeading(
            $p,
            $y,
            $elementStartLeft,
            $elementEndRight,
            $fontBold,
            $arrInput,
            $tableHeading
        );

        ### Variable Declaration ###
        $tbl = 0;
        $row = 0;
        $col1 = 1;
        $col2 = 2;

        // Coordinates for productProfile Table
        $leftX = $elementStartLeft;
        $leftY = 100;
        $rightX = $elementEndRight;
        $rightY = $y;

        // Define option lists
        $optlistTableTf = 'font=' . $fontRegular . ' fontsize=10 fillcolor=black wordspacing=0 leading=13';

        ### create table ###
        // add cell for every item in $tableContent
        foreach ($tableContent as $key => $value) {
            $row++;
            $normalFontsize = '10';

            ## Add $key cell ##
            $key = $this->replaceHtml(
                $key,
                $fontRegular,
                $fontBold,
                $normalFontsize
            );

            // Add new textflow
            $tf = $p->create_textflow($key, $optlistTableTf);
            if ($tf == 0) {
                throw new Exception('Error: ' . $p->get_errmsg());
            }

            // Add new table cell which contains the textflow
            $optlistTableCellLeft = 'colwidth=50% margintop=4 marginbottom=4 marginleft=4 marginright=4'
                . ' fittextflow={verticalalign=top} textflow=' . $tf;

            $tbl = $p->add_table_cell($tbl, $col1, $row, '', $optlistTableCellLeft);
            if ($tbl == 0) {
                throw new Exception('Error: ' . $p->get_errmsg());
            }

            ## Add $value cell ##
            $value = $this->replaceHtml(
                $value,
                $fontRegular,
                $fontBold,
                $normalFontsize
            );

            // add new textflow
            $tf = $p->create_textflow($value, $optlistTableTf);
            if ($tf == 0) {
                throw new Exception('Error: ' . $p->get_errmsg());
            }

            // Add new table cell which contains the textflow
            $optlistTableCellRight = 'colwidth=50% margintop=4 marginbottom=4 marginleft=4 marginright=4'
                . ' fittextflow={verticalalign=top} textflow=' . $tf;

            $tbl = $p->add_table_cell($tbl, $col2, $row, '', $optlistTableCellRight);
            if ($tbl == 0) {
                throw new Exception('Error: ' . $p->get_errmsg());
            }
        }

        // Output technical Values Table on page
        $optlistTable = 'rowheightdefault=15 '
            . 'stroke={{line=frame linewidth=1} {line=vertother linewidth=1} {line=horother linewidth=1}} ';

        $resultTable = $p->fit_table($tbl, $leftX, $leftY, $rightX, $rightY, $optlistTable);

        while ($resultTable != '_stop') {
            $this->generateNewPage(
                $p,
                $pagewidth,
                $pageheight,
                $y,
                $pageCount,
            );

            // Table coordinates for new page
            $leftX2 = $elementStartLeft;
            $leftY2 = 100;
            $rightX2 = $elementEndRight;
            $rightY2 = 755;

            // Place table on new page
            $resultTable = $p->fit_table($tbl, $leftX2, $leftY2, $rightX2, $rightY2, $optlistTable);
        }

        ## get height value of productProfile table ##
        // Get table height
        $resultTableY2 = $p->info_table($tbl, 'y2');

        // Return new height value
        $y = $resultTableY2 - 30;
    }

    private function loadImages(
        \PDFlib $p,
        int $elementStartLeft,
        int $elementEndRight,
        int $fontBold,
        int $y,
        array $arrInput,
        int $pageheight,
        int $pagewidth,
        &$pageCount
    ) {
        if ($y < 280) {
            $this->generateNewPage(
                $p,
                $pagewidth,
                $pageheight,
                $y,
                $pageCount,
            );
        }

        // Variable Declaration
        $imageHeading = $arrInput['image']['heading'];
        $imageSource = $arrInput['image']['source'];


        $this->createHeading(
            $p,
            $y,
            $elementStartLeft,
            $elementEndRight,
            $fontBold,
            $arrInput,
            $imageHeading
        );

        // start coordinates of the images
        $imageY = $y - 140;
        $imageX = $elementStartLeft;

        // load the image
        $image = $p->load_image('auto', $imageSource, '');
        if ($image == 0) {
            echo('Couldn not load logo image: ' . $p->get_errmsg());
            exit(1);
        }

        // place the image on the page
        $buf = 'scale=0.2 position={left}';
        $p->fit_image($image, $imageX, $imageY, $buf);
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
