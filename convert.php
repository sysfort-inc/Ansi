<?php

require_once 'convertToRoman.php';

function generateFooter($counter, $year, $isFirstFooter) {
    $footerContent = '<div class="footer footer-next">';
    $textPosition = '';
    if ($isFirstFooter) {
        
        $footerContent .= '<span class="footer-roman"><b>' . convertToRoman($counter) . '</b></span>';
        $footerContent .= '<span class="footer-text">© ISO ' . $year . ' – All rights reserved</span>';
        $textPosition = 'right'; // Indicate text position as right for first footer
    } else {
        
        $footerContent .= '<span class="footer-text">© ISO ' . $year . ' – All rights reserved</span>';
        $footerContent .= '<span class="footer-roman"><b>' . convertToRoman($counter) . '</b></span>';
        $textPosition = 'left'; // Indicate text position as left for subsequent footers
    }
    $footerContent .= '</div>';
    return array($footerContent, $textPosition); // Returning footer content and text position
}

$xmlFile = 'data.xml'; 
$reader = new XMLReader();

if (!$reader->open($xmlFile)) {
    die('Failed to open XML file.');
}

$html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Large XML to HTML</title>

    <style>
    html
    {
        background-color: gray;
    }

    body 
    {
        width: 793px;
        background-color: white;
        box-sizing: border-box;
        margin: 0 auto; 
        margin-top: 40px;
        margin-bottom:5px;
        border: 1px solid gray;
        padding:20px;
        border: hidden;
    }

    front iso-meta title-wrap full 
    {
        color: black;
        padding-top: 15px;
        margin-left: 325px;
    }

    hr
    {
        width: 60%;
    }

    .header
    {
        text-align: center;
        margin-top: 20px;
        padding-bottom: 10px;
        
    }

    .header span
    {
        font-weight: bold;
        display: inline-block; /* Ensure span width adjusts based on content */
        font-size: 18px; /* Increase font size */
    }

    .footer 
    {
        display: flex; 
        justify-content: space-between;
        width: 100%;
        text-align: center;
        bottom: 0;
        
    }

    .footer-roman 
    {
        bottom: 0;
        left: 0;
    }

    .page-break 
    {
        page-break-before: always;
        height:20px;
        width: 115%;
        margin: 0 auto; 
        background-color:gray;   
        break-before: always;
        overflow-x: auto;
        position: relative; 
        left: -40px;
        margin:5px;
    }
    </style>
</head>
<body>';

$contentHeight = 0;
$pageBreakThreshold = 800; 
$footerThreshold = 800; 
$hasPageBreak = false;

$romanCounter = 2; 
$isFirstFooter = true;
$year = null;
$headerText = null; 
$headerTextPosition = ''; // Store text position for header

while ($reader->read()) {
    if ($reader->nodeType == XMLReader::ELEMENT && $reader->name == 'copyright-year') {
        $reader->read();
        $year = $reader->value;
    }
    elseif ($reader->nodeType == XMLReader::ELEMENT && $reader->name == 'std-ref' && $reader->getAttribute('type') == 'dated') {
        $reader->read();
        $headerText = $reader->value;
    }
    
    if ($reader->nodeType == XMLReader::ELEMENT) {
        $html .= '<' . $reader->name;
        if ($reader->hasAttributes) {
            while ($reader->moveToNextAttribute()) {
                $html .= ' ' . $reader->name . '="' . $reader->value . '"';
            }
        }
        $html .= '>';
    } elseif ($reader->nodeType == XMLReader::TEXT) {
        $text = $reader->value;
        $contentHeight += 10; 

        if ($contentHeight > $pageBreakThreshold) {
            list($footer, $textPosition) = generateFooter($romanCounter++, $year, $isFirstFooter);
            $html .= $footer;
            $isFirstFooter = !$isFirstFooter; // Toggle for alternating footer arrangement
            $html .= '<div class="page-break"></div>';
            $html .= '<div class="header" style="text-align: ' . $textPosition . '"><span>' . $headerText . '</span></div>'; //  header div with dynamic content and aligned based on footer text position
            $contentHeight = 0;
        }

        if ($contentHeight > $footerThreshold) {
            $footerThreshold += 800; 
        }

        // Converting text to Roman numeral if it's a number in - footer
        if ($footerThreshold > 0 && is_numeric($text)) {
            $html .= convertToRoman((int)$text);
        } else {
            $html .= $text;
        }
    } elseif ($reader->nodeType == XMLReader::END_ELEMENT) {
        $html .= '</' . $reader->name . '>';
    }
}

$html .= <<<EOT
<script>
    //  JS code
    full = document.getElementsByTagName("full");
    full[0].style = "font-size:26px; font-weight:800; display:inline-flex;width:60%; height:200px;align-items:flex-end;justify-content:flex-end;";
    full[1].style = "font-style:italic;display:inline-flex; height:200px;align-items:flex-start;justify-content:flex-start; " ;

    intro = document.getElementsByTagName("intro");
    for (var i = 0; i < intro.length; i++) {
        intro[i].style.display = "none";
    }

    main = document.getElementsByTagName("main");
    for (var i = 0; i < 2; i++) {
        main[i].style.display = "none";
    }

    let hr1 = document.createElement("hr");
    let hr2 = document.createElement("hr");
    let hr3 = document.createElement("hr");
    let title_wrap = document.getElementsByTagName("title-wrap");

    let hr_div = document.createElement("div");
    hr_div.appendChild(hr1);
    hr_div.appendChild(hr2);
    hr_div.appendChild(hr3);
    hr1.classList.add("hr");
    hr2.classList.add("hr");
    hr3.classList.add("hr");
    //doc.findElementByAttribute("myAttribute", "aValue");

    title_wrap[0].insertBefore(hr_div, title_wrap[0].intro);
    hr_div.setAttribute("id", "hr_div");      

    let iso_meta = document.getElementsByTagName("iso-meta");
    let nodeList = iso_meta[0].children;
    console.log(nodeList);
    for(let i = 2;i<nodeList.length; i++){
        nodeList[i].style.display = "none";
    }


</script>
EOT;


$html .= '</body>
</html>';

$reader->close();

file_put_contents('output.html', $html);

echo 'Conversion successful. HTML file generated.';
?>
