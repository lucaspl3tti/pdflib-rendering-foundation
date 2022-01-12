<?php

return [
    'startTemplate' => 'assets/templates/template_example.pdf',
    'documentInfo' => [
        'Example PDF',
        'Example PDF Rendering',
        'Jan-Luca Splettstößer',
        'Jan-Luca Splettstößer',
    ],
    'headline' => 'PDF-Rendering Example',
    'paragraph' => '<p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, <b>sed diam nonumy eirmod tempor</b> invidunt ut labore et dolore magna aliquyam<sup>2</sup> erat, sed diam voluptua. At vero eos et <u>accusam et justo duo</u> <i>dolores et ea rebum.</i> Stet clita kasd gubergren, no sea <s>takimata sanctus est</s> Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur<sub>3</sub> sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</p>',
    'graphics' => [
        'images/database-file.svg',
        'images/it.svg',
        'images/paperclip.svg',
    ],
    'image' => [
        'heading' => 'Image Example',
        'source' => 'images/pexels-suzy-hazelwood-1629236.jpg',
    ],
    'table' => [
        'tableHeading' => 'Table Example',
        'tableContent' => [
            'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren' => '<p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, <b>sed diam nonumy eirmod tempor</b> invidunt ut labore et dolore magna aliquyam<sup>2</sup> erat, sed diam voluptua. At vero eos et <u>accusam et justo duo</u> <i>dolores et ea rebum.</i> Stet clita kasd gubergren, no sea <s>takimata sanctus est</s> Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur<sub>3</sub> sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</p>',
            '<p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, <b>sed diam nonumy eirmod tempor</b> invidunt ut labore et dolore magna aliquyam<sup>2</sup> erat, sed diam voluptua. At vero eos et <u>accusam et justo duo</u> <i>dolores et ea rebum.</i> Stet clita kasd gubergren, no sea <s>takimata sanctus est</s> Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur<sub>3</sub> sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</p>' => '<p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, <b>sed diam nonumy eirmod tempor</b> invidunt ut labore et dolore magna aliquyam<sup>2</sup> erat, sed diam voluptua. At vero eos et <u>accusam et justo duo</u> <i>dolores et ea rebum.</i> Stet clita kasd gubergren, no sea <s>takimata sanctus est</s> Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur<sub>3</sub> sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</p>',
            'Lorem ipsum dolor sit amet' => '<p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, <b>sed diam nonumy eirmod tempor</b> invidunt ut labore et dolore magna aliquyam<sup>2</sup> erat, sed diam voluptua. At vero eos et <u>accusam et justo duo</u> <i>dolores et ea rebum.</i> Stet clita kasd gubergren, no sea <s>takimata sanctus est</s> Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur<sub>3</sub> sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</p>',
        ],
    ],
];
