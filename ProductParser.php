<?php

class ProductParser
{

    public function parse(array $product): array {
        $category = $this->getCategory((int) $product['merchandiseGroup']);
        $sku = $this->getSKU($product['number']??null);
        $result = [
            'ean' => $this->getEAN($product['ean']),
            'category' => $category,
            'sku' => [
                'sku' => $sku,
                'name' => $product['name']??null,
                'type' => $this->getSKUType($product,$category),
            ],
            'width' => self::convertMetrics($product['measurements']['width']),
            'height' => self::convertMetrics($product['measurements']['height']),
            'length' => self::convertMetrics($product['measurements']['length']),
            'weight' => self::convertWeight($product['measurements']['weight']),
        ];

        return $result;
    }

    private static function convertMetrics($metric):int {
        return intval($metric * 10);
    }

    private static function convertWeight($weight):int {
        return intval($weight * 1000000);
    }

    private function getSKU($sku){
        if (empty($sku)) {
            throw new Exception('Unknown SKU');
        }
        return $sku;
    }

    private function getEAN($ean){
        if (str_starts_with($ean, '7') && strlen($ean) === 12){
            return '0' . $ean;
        }
        return $ean??null;
    }

    private function getCategory(int $categoryId){
        if (!isset(config('product_categories')[$categoryId])) {
            throw new Exception('Unknown category');
        }
        return config('product_categories')[$categoryId];
    }

    private function getSKUType(array $product, array $category):string {
        if (str_ends_with($product['number'], '_X') || $product['merchandiseGroup'] === '12') {
            return 'upsell';
        } 
        if ($product['isMatrixProduct'] 
            || in_array($category, ['bundles', 'external-services', 'services', 'discounts', 'digitals'])) {
            return 'main';
        }
        if (str_ends_with($product['number'], '_UL')) {
            return 'unlabeled';
        }
        return 'single';
    }
}