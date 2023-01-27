<?php

return [
    'documentInfo' => [
        'Example PDF',
        'Example PDF Rendering',
        'Twocream',
        'Twocream',
    ],
    'templates' => [
        'templateMain' => '/templates/template_example.pdf',
    ],
    'headline' => 'PDF-Rendering Example',
    'paragraph' => '<p>Lorem ipsum <strong>dolor sit amet</strong>, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</p>',
    'graphics' => [
        'images/database-file.svg',
        'images/it.svg',
    ],
    'image' => [
        'heading' => 'Image Example',
        'source' => 'images/pexels-suzy-hazelwood-1629236.jpg',
    ],
    'table' => [
        'tableHeading' => 'Table Example',
        'tableContent' => [
            'Lorem ipsum' => 'dolor sit amet',
            'consetetur' => 'sadipscing elitr',
            'sed diam' => 'nonumy eirmod tempor invidunt',
        ],
    ],
];
