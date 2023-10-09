<?php
/** @noinspection PhpUndefinedClassInspection */
namespace Disual\StaticProducts\Magento1Connector;

use Disual\StaticProducts\Core\AppConfig;
use Disual\StaticProducts\Core\AppLogger;
use Disual\StaticProducts\Core\AppUtils;
use Disual\StaticProducts\Helpers\CacheHelper;
use Mage;
use Mage_Core_Model_App;
use Mage_Catalog_Model_Product;
use Monolog\Logger;

class Magento1Connector {


  protected Logger $logger;

  public function __construct(int $storeId = 0) {
    // --- Magento APP

    /** @noinspection PhpIncludeInspection */
    require_once APP_ROOT . '/../app/Mage.php';
    umask(0);

    if ($storeId == 0) {
      $storeId = Mage_Core_Model_App::ADMIN_STORE_ID;
    }

    Mage::app()->setCurrentStore($storeId);
    $this->logger = AppLogger::getInstance()->getLogger();
  }

  public function getProductExtended(string $sku):?array {

    $textAttributes = ['certificazioni', 'colore', 'colore_casco', 'colore_frontale', 'descrizione_colore', 'ean', 'materiale_casco', 'mpn', 'nome_modello', 'peso_casco', 'stagione', 'taglia', 'url_key'];
    $selectAttributes = ['manufacturer', 'forma', 'materiale_aste', 'materiale_frontale', 'materiale_lenti', 'collezione'];
    $multiSelectAttributes = ['genere_new', 'tipologia_lente_multi'];
    $boolAttributes = ['edizione_limitata', 'pieghevole'];


    //
    // =============================== STEP: Raccolta dati
    //

    //
    // -- Dati del prodotto
    //
    $_product = Mage::getModel('catalog/product')->loadByAttribute('sku',$sku);
    if (!$_product) {
      Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
      $_product = Mage::getModel('catalog/product')->loadByAttribute('sku',$sku);
    }
    if (!$_product) {
      return null;
    }

    $productData = $_product->getData();

    $mediaApi      =  Mage::getModel("catalog/product_attribute_media_api");
    $mediaApiItems = $mediaApi->items($_product->getId());
    $image = '';
    if (!empty($mediaApiItems))  {
      $image = APP_ROOT . "/../media/catalog/product" . $mediaApiItems[0]['file'];
      // $image = base64_encode(file_get_contents($mainImage));
    }

    //
    // -- attributi tipo select (combo)
    //
    $allAttributes = [];
    foreach ($selectAttributes As $attributeName) {
      $allValues = $this->getAttributeValuesRaw($attributeName, true);
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

    foreach ($multiSelectAttributes As $attributeName) {
      $allValues = $this->getAttributeValuesRaw($attributeName, true);

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

    foreach ($textAttributes As $textAttribute) {
      $allAttributes[$textAttribute] = [
        'id' => '',
        'value' => $productData[$textAttribute],
        'type' => 'text'
      ];
    }

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
      'image' => $image,
      'attributes' => $allAttributes,
    ];
    return $result;
  }

  /**
   * @param string $sku
   * @param string $languageCode
   * @return array|null
   */
  public function getProduct(string $sku, string $languageCode = ''):?array {

    if (isset(AppConfig::LANGS_X[$languageCode])) {
      $storeId = AppConfig::LANGS_X[$languageCode]['store_id'];
    } else {
      // $storeId = Mage_Core_Model_App::ADMIN_STORE_ID;
      $storeId = AppConfig::LANGS_X[AppConfig::DEFAULT_LANG]['store_id'];
    }
    Mage::app()->setCurrentStore($storeId);
    $_product = Mage::getModel('catalog/product')->loadByAttribute('sku',$sku);
    if (!$_product) {
      Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
      $_product = Mage::getModel('catalog/product')->loadByAttribute('sku',$sku);
    }

    if (!$_product) {
      return null;
    }

    $mediaApi      =  Mage::getModel("catalog/product_attribute_media_api");
    $mediaApiItems = $mediaApi->items($_product->getId());
    $image = '';
    if (!empty($mediaApiItems))  {
      $image = APP_ROOT . "/../media/catalog/product" . $mediaApiItems[0]['file'];
    }

    return [
      'product' => $_product->getData(),
      'image' => $image
    ];
  }


  /**
   * @param string $attributeName
   * @param bool $useCache
   * @return array
   */
  public function getAttributeValuesRaw(string $attributeName, bool $useCache = false): array {

    if ($useCache) {
      $options = CacheHelper::getAttributeValuesRaw($attributeName);
      if ($options) {
        return $options;
      }
    }

    //Mage::app()->setCurrentStore(1);
    $attribute = Mage::getModel('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attributeName);
    return $attribute->getSource()->getAllOptions();
  }

  public function getProductAttributeValue(string $sku, string $attribute, int $storeId) {

    $_product = Mage::getModel('catalog/product')->loadByAttribute('sku',$sku);
    $storeIds = $_product->getStoreIds();
    foreach($storeIds as $_storeId){
      // quale lingua
      if ($_storeId == $storeId) {
        return Mage::getResourceModel('catalog/product')->getAttributeRawValue($_product->getId(), $attribute, $storeId);
      }
    }
    return null;
  }

  public function getTranslation(string $sku, string $attribute, int $storeId): string {

//    $_product = Mage::getModel('catalog/product')->loadByAttribute('sku',$sku);
//    $storeIds = $_product->getStoreIds();
//
//    foreach($storeIds as $storeId){
//      echo Mage::getResourceModel('catalog/product')->getAttributeRawValue($_product->getId(), 'description', $storeId);
//    }


    Mage::app()->setCurrentStore(Mage::getModel('core/store')->load($storeId));
    $_product = Mage::getModel('catalog/product')->loadByAttribute('sku',$sku);
//    if ($storeId == 2) {
//      echo __LINE__."\n";
//      print_r($_product->getData());
//      echo __LINE__."\n";
//      die();
//    }
    if (!$_product) {
      Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
      $_product = Mage::getModel('catalog/product')->loadByAttribute('sku',$sku);
    }
    if (!$_product) {
      return '';
    }
    $data = $_product->getData();


    if (isset($data[$attribute])) {
      return $data[$attribute];
    }
    return '';
  }

  public function deleteProductExtended(string $sku, string &$error = ''): bool {

    $_product = Mage::getModel('catalog/product')->loadByAttribute('sku',$sku);
    if (!$_product) {
      $error = "Product not found [$sku]";
      return false;
    }

    // Delete product images
    $mediaGallery = $_product->getMediaGalleryEntries();
    foreach ($mediaGallery as $mediaGalleryEntry) {
      $imagePath = Mage::getBaseDir('media') . '/catalog/product' . $mediaGalleryEntry->getFile();
      if (file_exists($imagePath)) {
        echo "Unlinking image: [$imagePath]\n";
        unlink($imagePath);
      }
    }

    // Delete product
    $_product->delete();
    return true;
  }


}
