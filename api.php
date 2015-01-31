<?php
/**
 * Trying to clean text from unknown character
 * @param $value
 * @return string
 */
function clean($value)
{
    $value = str_replace("  ", "", $value);
    $value = str_replace("\t", "", $value);
    $value = str_replace("\n\n", "", $value);
    $value = str_replace("\r\r", "", $value);
    return trim($value, chr(0x0A) . chr(0xC2) . chr(0xA0) . "\n\r\0\x0B\t");
}

/**
 * Get the HTML content of the invoice
 * @param $customer_id
 * @return string
 */
function getRawInvoice($customer_id)
{
    //end point to get the last invoice
    $url = "https://www.steg.com.tn/fr/espace/consult_fact.php?idcompt=";
    return file_get_contents($url . $customer_id);
}

/**
 * Convert the html into a DOM object and return TABLE nodes
 * @param $raw_html
 * @return DOMNodeList
 */
function getDomInvoice($raw_html)
{
    $dom = new DOMDocument();
    $dom->strictErrorChecking = false;
    $dom->loadHTML($raw_html);
    $dom->preserveWhiteSpace = false;
    return $dom->getElementsByTagName("table");
}

/**
 * Return due date of the invoice
 * @param $invoice_dom
 * @return mixed
 */
function getDueDate($invoice_dom)
{
    $rows = $invoice_dom->item(15)->getElementsByTagName("tr");
    foreach ($rows as $row) {
        $cols = $row->getElementsByTagName('td');
        $due_date = str_replace("-", "", clean($cols->item(0)->nodeValue));
    }
    return $due_date;
}

/**
 * Return the total of invoice
 * @param $invoice_dom
 * @return string
 */
function getTotal($invoice_dom)
{
    $rows = $invoice_dom->item(13)->getElementsByTagName("tr");
    foreach ($rows as $row) {
        $cols = $row->getElementsByTagName('td');
        $total = clean($cols->item(0)->nodeValue);
    }
    return $total;
}

$customer_id = ""; //Customer ID
$invoice_raw = getRawInvoice($customer_id);
$invoice_dom = getDomInvoice($invoice_raw);
$due_date = getDueDate($invoice_dom);
$total = getTotal($invoice_dom);

echo "Customer ID : ".$customer_id."<br>";
echo "Total : ".$total."<br>";
echo "Due date : ".$due_date."<br>";
