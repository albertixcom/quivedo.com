<?php /** @noinspection PhpFullyQualifiedNameUsageInspection */

/** @noinspection PhpUndefinedClassInspection */

namespace Disual\CustomApi\Magento1Connector;

use Disual\CustomApi\Core\AppLogger;
use Disual\CustomApi\Core\AppUtils;
use Monolog\Logger;
use Mage;
use Mage_Catalog_Model_Product;
use Mage_Core_Model_App;

class Magento1Connector {

  protected Logger $logger;

  public function __construct() {
    // --- Magento APP
    require_once APP_ROOT . '/../app/Mage.php';
    umask(0);

    /** @noinspection PhpUndefinedClassInspection */
    Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
    $this->logger = AppLogger::getInstance()->getLogger();
  }

  public function getRandomProducts() {
    $min = 8709;
    $max = 150895;
    $total = 50;

    $resource = Mage::getModel('catalog/product');
    $resource->setStoreId(1);
    $arr = [];
    for($i=1; $i<$total; $i++) {
      $done = false;
      //echo "($i/$total) ...\n";
      do {
        $randId = rand($min, $max);
        //echo "\$randId: [$randId]\n";
        $_product = $resource->load($randId);

        if ($_product) {
          //$productData = $_product->getData();
          $stockItem = $_product->getStockItem();
          $stockData = $stockItem->getData();
          $qty = (int)$stockData['qty'];
          $sku = $_product->getSku();
          if ($qty>0&&!in_array($sku, $arr)) {
            $arr[] = $sku;
            $done = true;
            echo $sku."\n";
          }
        }
      } while (!$done);
    }

    print_r($arr);

    die();

  }

  public function getProductExtended(string $sku, int $storeId = 0):?array {

    $textAttributes = ['certificazioni', 'colore', 'colore_casco', 'colore_frontale', 'descrizione_colore', 'ean', 'materiale_casco', 'mpn', 'nome_modello', 'peso_casco', 'stagione', 'taglia', 'url_key', 'codice_occhiale',
      'codice_colore',
      'codice_lente'];
    $selectAttributes = ['spedizione', 'manufacturer', 'forma', 'materiale_aste', 'materiale_frontale', 'materiale_lenti', 'collezione', 'fornitore'];
    $multiSelectAttributes = ['genere_new', 'tipologia_lente_multi', 'famiglia_colore_casco', 'famiglia_colore_lente', 'famiglia_colore_montatura'];
    $boolAttributes = ['edizione_limitata', 'pieghevole', 'flex_asta', 'graduabile','nascondi_prezzo_listino'];

//    $productCollection = Mage::getModel('catalog/product')
//      ->setStoreId(1)
//      ->getCollection()
//      ->addAttributeToSelect('*') // Seleziona tutti gli attributi del prodotto
//      ->addAttributeToFilter('description', array('notnull' => true));
//
//    foreach ($productCollection as $product) {
//      echo "ID Prodotto: " . $product->getId() . "<br>";
//      echo "Nome Prodotto: " . $product->getName() . "<br>";
//      echo "Descrizione: " . $product->getDescription() . "<br>";
//      echo "<br>";
//      die();
//    }


    //
    // Prodotto base
    //
    $resource = Mage::getModel('catalog/product');
    if ($storeId > 0) {
      $resource->setStoreId($storeId);
    }
    $_product = $resource->loadByAttribute('sku',$sku);
    if (!$_product) {
      Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
      $_product = Mage::getModel('catalog/product')->loadByAttribute('sku',$sku);
    }
    if (!$_product) {
      return null;
    }
    $productData = $_product->getData();

    //
    // Immagini (urls)
    //
    $mediaApi      =  Mage::getModel("catalog/product_attribute_media_api");
    $mediaApiItems = $mediaApi->items($_product->getId());
    $images = [];
    if (!empty($mediaApiItems))  {
      foreach ($mediaApiItems As $mediaApiItem) {
        $images[] = $mediaApiItem['url'];
      }
    }

    //
    // -- attributi tipo select (combo)
    //
    $allAttributes = [];
    foreach ($selectAttributes As $attributeName) {
      $allValues = $this->getAttributeValuesRaw($attributeName);
      $attrValId = (int)$productData[$attributeName];
      if ($attrValId > 0) {
        $value = AppUtils::searchInArrayEx($attrValId, $allValues, 'value');
        if ($value) {
          $allAttributes[$attributeName] = [
            'id' => $value['value'],
            'value' => $value['label'],
            'type' => 'select'
          ];
        }
      }
    }

    //
    // -- attributi tipo multiselect
    //
    foreach ($multiSelectAttributes As $attributeName) {
      $allValues = $this->getAttributeValuesRaw($attributeName);

      $_value = trim($productData[$attributeName]);
      if (empty($_value)) {
        continue;
      }
      $arr = explode(',', $_value);
      foreach ($arr As $item) {
        $attrValId = (int)$item;
        $value = AppUtils::searchInArrayEx($attrValId, $allValues, 'value');
        if ($value) {
          $allAttributes[$attributeName]['values'][] = [
            'id' => $value['value'],
            'value' => $value['label'],
          ];
        }
      }
      if (isset($allAttributes[$attributeName])) {
        $allAttributes[$attributeName]['type'] = 'multiselect';
      }
    }

    //
    // -- attributi tipo testo
    //
    foreach ($textAttributes As $textAttribute) {
      $allAttributes[$textAttribute] = [
        'id' => '',
        'value' => $productData[$textAttribute],
        'type' => 'text'
      ];
    }

    //
    // -- attributi tipo Si/No
    //
    foreach ($boolAttributes As $boolAttribute) {
      $allAttributes[$boolAttribute] = [
        'id' => '',
        'value' => ($productData[$boolAttribute] == 1)?'Yes':'No',
        'type' => 'boolean'
      ];
    }

    /** @noinspection PhpUnnecessaryLocalVariableInspection */
    $result = [
      'product_id' => $productData['entity_id'],
      'sku' => $productData['sku'],
      'name' => $productData['name'],
      'description' => $productData['description'],
      'images' => $images,
      'special_price' => $productData['special_price'],
      'price' => $productData['price'],
      'attributes' => $allAttributes,
      'categories' => $_product->getCategoryIds(),
      'meta' => [
        'title' => $productData['meta_title'],
        'description' => $productData['meta_description']
      ]
    ];
    return $result;
  }

  public function updateSize(string $sku, string $size, string $action): bool {
    $product = Mage::getModel('catalog/product')->loadByAttribute('sku',$sku);

    if(!$product){
      $this->logger->debug("!!! ERROR: prodotto non trovato");
      return false;
    }

    $option = $this->getProductOptionSize($product);

    if ($option) {
      foreach ($option->getValues() As $optionValue) {
        if (strcasecmp($optionValue->getTitle(), $size) === 0) {

          // Yuhuuu !! trovata
          if ($action == "remove") {
            $this->logger->debug("Deleteing size [".$optionValue->getTitle()."]");
            $optionValue->delete();
          }
          return true;
        }
      }

      // non trovata
      if ($action == "add") {
        $data['title'] = $size;
        $this->addOptionSize($option, $data);
        return true;
      }
    } else {
      $data[] = ['title' => $size];
      $this->addOptionSizes($product, $data);
    }

    return true;
  }

  public function addOptionSize($option, array $data): bool {

    $sizeName = $data['title']??'';
    if (empty($sizeName)) {
      return false;
    }

    $valueInstance = Mage::getModel('catalog/product_option_value');
    $valueInstance->addValue([
      'title' => $sizeName,
      'price' => 0,
      'price_type' => 'fixed',
      'sku' => '',
      'ean' => '',
      'qty' => $data['qty']??0,
      'sort_order' => 0,
    ]);

    $valueInstance->setOption($option);
    try {
      $valueInstance->saveValues();
      $this->logger->debug("Size [$sizeName] added.");
    } catch (Exception $e) {
      $this->logger->error("!!! ERROR > ".$e->getMessage());
      return false;
    }
    return true;
  }

  /**
   * @param string $sku
   * @param int $movement
   * @param array $sizes
   * @return bool
   */
  public function updateStock(string $sku, int $movement, array $sizes): bool {

    $product = Mage::getModel('catalog/product')->loadByAttribute('sku',$sku);
    if(!$product){
      $this->logger->debug("!!! ERROR: prodotto non trovato");
      return false;
    }
    $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId());

    $stockQty = $stockItem->getQty() + $movement;

    $debug = "Updating stock. SKU: [$sku], \$movement [$movement], new qty: [$stockQty]";
    $this->logger->debug($debug);

    $stockItem->setData('use_config_manage_stock', 0);
    $stockItem->setData('manage_stock', 1);
    $stockItem->setData('is_in_stock', (int)($stockQty > 0));
    $stockItem->setData('qty', $stockQty);
    $stockItem->save();

    // controllo sizes
    $option = $this->getProductOptionSize($product);
    if ($option) {
      $this->updateOptionSizes($option, $sizes);
    } else {
      if (!empty($sizes)) {
        $data = [];
        foreach ($sizes As $sizeName) {
          $data[] = ['title' => $sizeName['title']];
        }

        $this->addOptionSizes($product, $data);
      }
    }
    return true;
  }

  /**
   * @param $option
   * @param array $sizes
   * @return bool
   * @noinspection PhpReturnValueOfMethodIsNeverUsedInspection
   */
  private function updateOptionSizes($option, array $sizes): bool {

    //
    // check taglie nuove
    //
    foreach ($sizes As $sizeItem) {
      $sizeName = $sizeItem['title'];
      $qty = $sizeItem['qty'];
      $found = false;
      foreach ($option->getValues() As $optionValue) {
        if (strcasecmp($optionValue->getTitle(), $sizeName) === 0) {
          if ((int)$optionValue->getQty()!=(int)$qty) {
            $optionValue->setQty($qty);
            $optionValue->save();
          }
          $found = true;
          break;
        }
      }
      if (!$found) {
        $this->logger->debug("Size [$sizeName] not found. To be added");
        $data = ['title' => $sizeName, 'qty' => $qty ];
        $this->addOptionSize($option, $data);
      }
    }

    //
    // check da cancellare
    //
    foreach ($option->getValues() As $optionValue) {
      $found = false;
      foreach ($sizes As $sizeItem) {
        $sizeName = $sizeItem['title'];
        if (strcasecmp($optionValue->getTitle(), $sizeName) === 0) {
          $found = true;
          break;
        }
      }
      if (!$found) {
        try {
          $this->logger->debug("Deleteing size [".$optionValue->getTitle()."]");
          $optionValue->delete();
        } catch (Exception $e) {
          $this->logger->error("!!! ERROR > ".$e->getMessage());
          return false;
        }
      }
      // in teoria si potrebbe anche eliminare complettamente
//      if (empty($sizes)) {
//        $option->delete();
//      }
    }
    return true;
  }

  /**
   * @param $product
   * @param array $data
   * @return bool
   * @noinspection PhpReturnValueOfMethodIsNeverUsedInspection
   */
  private function addOptionSizes($product, array $data): bool {
    $optionData = [
      'title' => 'Size',
      'type' => 'drop_down',
      'is_require' => 1,
      'sort_order' => 0,
      'values' => []
    ];
    foreach ($data As $item) {
      $title = $item['title']??'';
      if (empty($title)) {
        continue;
      }
      $optionData['values'][] =   [
        'title' => $title,
        'price' => 0,
        'price_type' => 'fixed',
        'sku' => '',
        'ean' => '',
        'qty' => $item['qty']??'',
        'sort_order' => 0,
      ];
    }

    try {

      $optionInstance = $product->getOptionInstance();
      $optionInstance->addOption($optionData);
      $optionInstance->setProduct($product);

      $product->setHasOptions(1);
      $product->save();

      return true;
    } catch (Exception $e) {
      $this->logger->error("!!! ERROR > ".$e->getMessage());
      return false;
    }
  }

  private function getProductOptionSize($product) {
    $option = null;
    foreach ($product->getProductOptionsCollection() as $_option) {
      if (strtolower($_option->getTitle()) == "size") {
        $option = $_option;
        break;
      }
    }
    return $option;
  }

  /** @noinspection PhpUnused */
  public function getAttributeValues(string $attributName): array {
    $attribute = Mage::getModel('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attributName);
    $list = $attribute->getSource()->getAllOptions();
    $values = [];
    foreach ($list as $opt) {
      $name = $opt['label'];
      $id = $opt['value'];
      if (!empty($id)) {
        $values[$id] = $name;
      }
    }
    return $values;
  }

  /**
   * @param string $sku
   * @param array $productData
   * @return bool
   */
  public function updateProductData(string $sku, array $productData): bool {

    $product = Mage::getModel('catalog/product')->loadByAttribute('sku',$sku);
    if(!$product){
      $debug = "!!! ERRORE: prodotto non trovato\n";
      echo $debug."\n";
      $this->logger->debug($debug);
      return false;
    }

    foreach ($productData as $key => $value) {
      $product->setData($key, $value);
    }
    $product->save();

    return true;
  }

  public function listCategories(int $storeId = 0, bool $onlyActive = false): array {
    if ($storeId > 0) {
      Mage::app()->setCurrentStore($storeId);
    }

    $categoryCollection = Mage::getModel('catalog/category')
      ->getCollection()
      ->addAttributeToSelect('*');

    if ($onlyActive) {
      $categoryCollection->addIsActiveFilter();
    }

    $categories = [];
    foreach ($categoryCollection as $category) {
      $categories[] = $category->getData();
    }

    return $categories;
  }

  public function categoriesTree(int $storeId = 0, bool $onlyActive = false): array {
    return $this->getCategoryTree(1, 0, $storeId, $onlyActive);
  }


  private function getCategoryTree($parentId = 1, $level = 0, int $storeId = 0, bool $onlyActive = false) {
    $tree = array();
    if ($storeId > 0) {
      Mage::app()->setCurrentStore($storeId);
    }
    $categories = Mage::getModel('catalog/category')
      ->getCollection()
      ->addAttributeToSelect('*')
      ->addAttributeToFilter('parent_id', $parentId)
      ->addAttributeToSort('position', 'asc');
    if ($onlyActive) {
      $categories->addAttributeToFilter('is_active', 1);
    }

    foreach ($categories as $category) {
      $categoryData = $category->getData();
      $categoryData['level'] = $level;

      $subcategories = $this->getCategoryTree($category->getId(), $level + 1, $storeId, $onlyActive);
      if (!empty($subcategories)) {
        $categoryData['children'] = $subcategories;
      }

      $tree[] = $categoryData;
    }

    return $tree;
  }

  /// --------------- PRIVATE

  /**
   * @param string $attributeName
   * @return array
   */
  public function getAttributeValuesRaw(string $attributeName): array {
    $storeId = 1;


    //Mage::app()->setCurrentStore(1);
    $attribute = Mage::getModel('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attributeName);
    return $attribute->setStoreId($storeId)->getSource()->getAllOptions();
  }

  public function getSizesOption($product) {
    foreach ($product->getProductOptionsCollection() as $_option) {
      if (strtolower($_option->getTitle()) == "size") {
        return $_option;
      }
    }
    return null;
  }


}
