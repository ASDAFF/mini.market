<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die(); ?>

<?php

/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CCacheManager $CACHE_MANAGER
 * @global CDatabase $DB
 * @param array $arParams
 * @param array $arResult
 */

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

// get measures
$arResult['MEASURES'] = array();
$rsMeasure = CCatalogMeasure::GetList();
while ($arMeasure = $rsMeasure->Fetch())
{
	$arResult['MEASURES'][$arMeasure['ID']] = $arMeasure['SYMBOL_RUS'];
}

if (count($arParams['PRICE_CODE']) === 1)
{
	$priceCode = $arParams['PRICE_CODE'][0];
}

$item = $arResult;

// get image url
$item['PIC'] = SITE_DIR . 'images/default-product.jpg';
if (!empty($item['PREVIEW_PICTURE']))
{
	$item['PIC_ID'] = $item['PREVIEW_PICTURE']['ID'];
	$item['PIC'] = $item['PREVIEW_PICTURE']['SRC'];
}
if (empty($item['PIC_ID']) && !empty($item['DETAIL_PICTURE']))
{
	$item['PIC_ID'] = $item['DETAIL_PICTURE']['ID'];
	$item['PIC'] = $item['DETAIL_PICTURE']['SRC'];
}

// get product additional properties
$item['PACKING'] = Loc::getMessage('PRMEDIA_MM_CEE_FOR_DEFAULT');
$item['STEP'] = 1;
$productFilter = array(
	'ID' => $item['ID']
);
$rsProduct = CCatalogProduct::GetList(array(), $productFilter);
if ($arProduct = $rsProduct->Fetch())
{
	$measureId = intval($arProduct['MEASURE']);
	if ($measureId > 0)
	{
		$item['PACKING'] = $arResult['MEASURES'][$measureId];
		$ratioFilter = array(
			'ID' => $measureId,
			'PRODUCT_ID' => $item['ID']
		);
		$rsRatio = CCatalogMeasureRatio::GetList(array(), $ratioFilter);
		if ($arRatio = $rsRatio->Fetch())
		{
			$item['STEP'] = $arRatio['RATIO'];
		}
	}
}

// parse price
if (!empty($priceCode) && \Bitrix\Main\Loader::includeModule('prmedia.minimarket'))
{
	$price = $item['PRICES'][$priceCode];
	$item['PRICE_PARSED'] = CPrmediaMinimarketPriceHelper::parse($price['VALUE'], $price['CURRENCY']);
}

$arResult = $item;