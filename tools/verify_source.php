<?php
/**
 * Verifikasi mandiri dataset JSON tanpa Laravel/vendor.
 * Jalankan: php tools/verify_source.php
 */
$base = dirname(__DIR__).'/database/data';
$products = json_decode(file_get_contents($base.'/products.json'), true);
$transactions = json_decode(file_get_contents($base.'/transactions.json'), true);
$items = json_decode(file_get_contents($base.'/transaction_items.json'), true);
$expectedRules = json_decode(file_get_contents($base.'/expected_apriori_rules.json'), true);

$fail = function (string $message): never {
    fwrite(STDERR, "[GAGAL] {$message}\n");
    exit(1);
};

if (count($products) !== 15) $fail('Produk harus 15.');
if (count($transactions) !== 300) $fail('Transaksi harus 300.');
if (count($items) !== 841) $fail('Detail transaksi harus 841 baris.');
if (count($expectedRules) !== 17) $fail('Aturan layak sumber harus 17.');

$totals = [];
$baskets = [];
foreach ($items as $item) {
    $invoice = $item['invoice_number'];
    $totals[$invoice] = ($totals[$invoice] ?? 0) + (int) $item['subtotal'];
    $baskets[$invoice][$item['product_code']] = true;
}
foreach ($transactions as $transaction) {
    if (($totals[$transaction['invoice_number']] ?? -1) !== (int) $transaction['total']) {
        $fail('Subtotal detail tidak sama dengan total '.$transaction['invoice_number']);
    }
}

function combos(array $items, int $size): array {
    sort($items, SORT_NATURAL);
    $result=[];
    $walk=function($start,$current) use (&$walk,&$result,$items,$size){
        if(count($current)===$size){$result[]=$current;return;}
        $need=$size-count($current);
        for($i=$start;$i<=count($items)-$need;$i++){$next=$current;$next[]=$items[$i];$walk($i+1,$next);}
    };
    $walk(0,[]);
    return $result;
}
function containsAll(array $basket, array $candidate): bool { foreach($candidate as $c) if(!isset($basket[$c])) return false; return true; }
function keyOf(array $codes): string { sort($codes,SORT_NATURAL); return implode('|',$codes); }

$totalCount = count($baskets);
$allCodes = array_column($products, 'code');
$frequent=[]; $counts=[];
foreach ([1,2,3] as $size) {
    foreach (combos($allCodes,$size) as $candidate) {
        if ($size > 1) {
            $ok=true;
            foreach (combos($candidate,$size-1) as $subset) if(!isset($frequent[$size-1][keyOf($subset)])){$ok=false;break;}
            if(!$ok) continue;
        }
        $count=0; foreach($baskets as $basket) if(containsAll($basket,$candidate)) $count++;
        $counts[keyOf($candidate)]=$count;
        if($count/$totalCount >= .2 - 1e-12) $frequent[$size][keyOf($candidate)]=['codes'=>$candidate,'count'=>$count];
    }
}
if(count($frequent[1]??[])!==8) $fail('Frequent 1-itemset bukan 8.');
if(count($frequent[2]??[])!==8) $fail('Frequent 2-itemset bukan 8.');
if(count($frequent[3]??[])!==1) $fail('Frequent 3-itemset bukan 1.');

$rules=[];
foreach($frequent as $size=>$sets){
    if($size<2) continue;
    foreach($sets as $set){
        $codes=$set['codes']; $unionCount=$set['count'];
        for($aSize=1;$aSize<$size;$aSize++) foreach(combos($codes,$aSize) as $a){
            $b=array_values(array_diff($codes,$a)); sort($b,SORT_NATURAL);
            $ca=$counts[keyOf($a)]??0; $cb=$counts[keyOf($b)]??0;
            if(!$ca||!$cb) continue;
            $support=$unionCount/$totalCount; $confidence=$unionCount/$ca; $lift=$confidence/($cb/$totalCount);
            if($support>=.2-1e-12 && $confidence>=.6-1e-12 && $lift>1){$rules[keyOf($a).'->'.keyOf($b)]=[$support,$confidence,$lift];}
        }
    }
}
if(count($rules)!==17) $fail('Aturan hasil hitung bukan 17, melainkan '.count($rules).'.');
foreach($expectedRules as $expected){
    $key=keyOf($expected['antecedent_codes']).'->'.keyOf($expected['consequent_codes']);
    if(!isset($rules[$key])) $fail('Aturan '.$key.' hilang.');
    [$support,$confidence,$lift]=$rules[$key];
    if(abs($support-$expected['support'])>0.000001 || abs($confidence-$expected['confidence'])>0.000001 || abs($lift-$expected['lift'])>0.000001) $fail('Metrik aturan '.$key.' berbeda.');
}

echo "[OK] Dataset valid: 15 produk, 300 transaksi, 841 detail.\n";
echo "[OK] Apriori: F1=8, F2=8, F3=1, aturan layak=17.\n";
echo "[OK] Seluruh support/confidence/lift cocok dengan workbook sumber.\n";
