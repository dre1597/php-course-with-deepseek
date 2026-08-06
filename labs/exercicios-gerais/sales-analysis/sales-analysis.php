<?php


function calculateTotalSales($sales): float|int
{
    return array_sum(array_column($sales, 'amount'));
}

function calculateSellerWithHighestTotalSales($sales): string
{
    $totalBySeller = calculateTotalSalesBySeller($sales);
    return array_search(max($totalBySeller), $totalBySeller);
}

function calculateTotalSalesBySeller($sales): array
{
    return array_reduce($sales, function (array $totals, array $sale) {
        $totals[$sale['seller']] = ($totals[$sale['seller']] ?? 0) + $sale['amount'];

        return $totals;
    }, []);
}

function calculateMostVolumeMonth($sales): false|int|string
{
    $totalByMonth = calculateTotalSalesByMonth($sales);
    return array_search(max($totalByMonth), $totalByMonth);
}

function calculateTotalSalesByMonth($sales): array
{
    return array_reduce($sales, function (array $totals, array $sale) {
        $totals[$sale['month']] = ($totals[$sale['month']] ?? 0) + $sale['amount'];

        return $totals;
    }, []);
}

function calculateAverageSales($sales): float|int
{
    return calculateTotalSales($sales) / count($sales);
}
