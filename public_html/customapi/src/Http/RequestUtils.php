<?php
namespace Disual\CustomApi\Http;

use Psr\Http\Message\ServerRequestInterface;

class RequestUtils  {

  static array $browsers = [
    'SamsungBrowser'=> 'Samsung Browser',
    'MiuiBrowser'   => 'Xiaomi Browser',
    'NokiaBrowser'  => 'Nokia Browser',
    'UCBrowser'     => 'UC Browser',
    'OPR'           => 'Opera',
    'Flock'         => 'Flock',
    'Edge'          => 'Edge',
    'edg'           => 'Edge',
    'Maxthon'       => 'Maxthon',
    'Opera.*?Version' => 'Opera',
    'Opera'         => 'Opera',
    'MSIE'          => 'Internet Explorer',
    'Internet Explorer' => 'Internet Explorer',
    'Trident.* rv'  => 'Internet Explorer',
    'YaBrowser'     => 'Yandex Browser',
    'Chrome'        => 'Chrome',
    'Shiira'        => 'Shiira',
    'Firefox'       => 'Firefox',
    'Chimera'       => 'Chimera',
    'Phoenix'       => 'Phoenix',
    'Firebird'      => 'Firebird',
    'Camino'        => 'Camino',
    'Netscape'      => 'Netscape',
    'OmniWeb'       => 'OmniWeb',
    'Safari'        => 'Safari',
    'Mozilla'       => 'Mozilla',
    'Konqueror'     => 'Konqueror',
    'icab'          => 'iCab',
    'Lynx'          => 'Lynx',
    'Links'         => 'Links',
    'hotjava'       => 'HotJava',
    'amaya'         => 'Amaya',
    'IBrowse'       => 'IBrowse',
    'Ubuntu'        => 'Ubuntu Web Browser',
  ];

  static array $device_name = [
    'MI '           => 'MI',
    'vivo'          => 'Vivo',
    'Ubuntu'        => 'Ubuntu',
    'S25'           => 'Smart S-25',
    'Moto G'        => 'Moto G',
    'LAVA'          => 'LAVA',
    'RMX'           => 'Oppo Realme',
    'Lenovo'        => 'Lenovo',
    'iPhone'        => 'iPhone',
    'pixel'         => 'Google Pixel',
    'BB'            => 'BlackBerry',
    'HUAWEI'        => 'HUAWEI',
    'LM'            => 'LG',
    'LGM'           => 'LG',
    'LG'            => 'LG',
    'windows phone' => 'Windows Phone',
    'ASUS_'         => 'Asus ZenFone',
    'KFAPWI'        => 'Kindle Fire',
    'SM-'           => 'Samsung',
    'HONOR'         => 'Huawei Honor',
    'A37F'          => 'Oppo',
    'ONEPLUS'       => 'One Plus',
    'Karbonn'       => 'Karbonn',
    'XT'            => 'Motorola',
    'os x'          => 'Apple Mac',
    'mobileexplorer' => 'Mobile Explorer',
    'palmsource'    => 'Palm',
    'palmscape'     => 'Palmscape',
    'motorola'      => 'Motorola',
    'nokia'         => 'Nokia',
    'nexus'         => 'Nexus',
    'palm'          => 'Palm',

    'ipad'          => 'iPad',
    'ipod'          => 'Apple iPod Touch',
    'sony'          => 'Sony Ericsson',
    'ericsson'      => 'Sony Ericsson',
    'blackberry'    => 'BlackBerry',
    'cocoon'        => 'O2 Cocoon',
    'blazer'        => 'Treo',
    'lg'            => 'LG',
    'amoi'          => 'Amoi',
    'xda'           => 'XDA',
    'mda'           => 'MDA',
    'vario'         => 'Vario',
    'htc'           => 'HTC',
    'samsung'       => 'Samsung',
    'sharp'         => 'Sharp',
    'sie-'          => 'Siemens',
    'alcatel'       => 'Alcatel',
    'benq'          => 'BenQ',
    'ipaq'          => 'HP iPaq',
    'mot-'          => 'Motorola',
    'playstation portable'  => 'PlayStation Portable',
    'playstation 3'     => 'PlayStation 3',
    'playstation vita'      => 'PlayStation Vita',
    'hiptop'        => 'Danger Hiptop',
    'nec-'          => 'NEC',
    'panasonic'     => 'Panasonic',
    'philips'       => 'Philips',
    'sagem'         => 'Sagem',
    'sanyo'         => 'Sanyo',
    'spv'           => 'SPV',
    'zte'           => 'ZTE',
    'sendo'         => 'Sendo',
    'nintendo dsi'  => 'Nintendo DSi',
    'nintendo ds'   => 'Nintendo DS',
    'nintendo 3ds'  => 'Nintendo 3DS',
    'wii'           => 'Nintendo Wii',
    'open web'      => 'Open Web',
    'openweb'       => 'OpenWeb',
    'android'       => 'Android',
    'symbian'       => 'Symbian',
    'SymbianOS'     => 'SymbianOS',
    'elaine'        => 'Palm',
    'series60'      => 'Symbian S60',
    'windows ce'    => 'Windows CE',
    'windows'       => 'Windows',
    'obigo'         => 'Obigo',
    'netfront'      => 'Netfront Browser',
    'openwave'      => 'Openwave Browser',
    'mobilexplorer' => 'Mobile Explorer',
    'operamini'     => 'Opera Mini',
    'opera mini'    => 'Opera Mini',
    'opera mobi'    => 'Opera Mobile',
    'fennec'        => 'Firefox Mobile',
    'digital paths' => 'Digital Paths',
    'avantgo'       => 'AvantGo',
    'xiino'         => 'Xiino',
    'novarra'       => 'Novarra Transcoder',
    'vodafone'      => 'Vodafone',
    'docomo'        => 'NTT DoCoMo',
    'o2'            => 'O2',
    'mobile'        => 'Generic Mobile',
    'wireless'      => 'Generic Mobile',
    'j2me'          => 'Generic Mobile',
    'midp'          => 'Generic Mobile',
    'cldc'          => 'Generic Mobile',
    'up.link'       => 'Generic Mobile',
    'up.browser'    => 'Generic Mobile',
    'smartphone'    => 'Generic Mobile',
    'cellphone'     => 'Generic Mobile',
    'Linux'         => 'Linux'
  ];

  static array $os = [
    'windows nt 10.0'   => 'Windows 10',
    'windows nt 6.3'    => 'Windows 8.1',
    'windows nt 6.2'    => 'Windows 8',
    'windows nt 6.1'    => 'Windows 7',
    'windows nt 6.0'    => 'Windows Vista',
    'windows nt 5.2'    => 'Windows 2003',
    'windows nt 5.1'    => 'Windows XP',
    'windows nt 5.0'    => 'Windows 2000',
    'windows nt 4.0'    => 'Windows NT 4.0',
    'winnt4.0'          => 'Windows NT 4.0',
    'winnt 4.0'         => 'Windows NT',
    'winnt'             => 'Windows NT',
    'windows 98'        => 'Windows 98',
    'win98'             => 'Windows 98',
    'windows 95'        => 'Windows 95',
    'win95'             => 'Windows 95',
    'windows phone'     => 'Windows Phone',
    'windows'           => 'Unknown Windows OS',
    'android'           => 'Android',
    'blackberry'        => 'BlackBerry',
    'BB'                => 'BlackBerry',
    'iphone'            => 'iOS',
    'ipad'              => 'iOS',
    'ipod'              => 'iOS',
    'os x'              => 'Mac OS X',
    'ppc mac'           => 'Power PC Mac',
    'freebsd'           => 'FreeBSD',
    'ppc'               => 'Macintosh',
    'Linux'             => 'Linux',
    'debian'            => 'Debian',
    'sunos'             => 'Sun Solaris',
    'beos'              => 'BeOS',
    'apachebench'       => 'ApacheBench',
    'aix'               => 'AIX',
    'irix'              => 'Irix',
    'osf'               => 'DEC OSF',
    'hp-ux'             => 'HP-UX',
    'netbsd'            => 'NetBSD',
    'bsdi'              => 'BSDi',
    'openbsd'           => 'OpenBSD',
    'gnu'               => 'GNU/Linux',
    'unix'              => 'Unknown Unix OS',
    'symbian'           => 'Symbian OS',
    'Nokia'             => 'Nokia Mobile',
  ];

  /** @noinspection PhpUnused */
  public static function setParams(ServerRequestInterface $request, array $params): ServerRequestInterface {
    $query = preg_replace('|%5B[0-9]+%5D=|', '=', http_build_query($params));
    return $request->withUri($request->getUri()->withQuery($query));
  }

  public static function getHeader(ServerRequestInterface $request, string $header): string {
    $headers = $request->getHeader($header);
    return $headers[0] ?? '';
  }

  /** @noinspection PhpUnused */
  public static function getParams(ServerRequestInterface $request): array {
    $params = array();
    $query = $request->getUri()->getQuery();
    //$query = str_replace('][]=', ']=', str_replace('=', '[]=', $query));
    $query = str_replace('%5D%5B%5D=', '%5D=', str_replace('=', '%5B%5D=', $query));
    parse_str($query, $params);
    return $params;
  }

  public static function getPathSegment(ServerRequestInterface $request, int $part): string {
    $path = $request->getUri()->getPath();
    $pathSegments = explode('/', rtrim($path, '/'));
    if ($part < 0 || $part >= count($pathSegments)) {
      return '';
    }
    return urldecode($pathSegments[$part]);
  }

  /** @noinspection PhpUnused */
  public static function toString(ServerRequestInterface $request): string {
    $method = $request->getMethod();
    $uri = $request->getUri()->__toString();
    $headers = $request->getHeaders();
    $request->getBody()->rewind();
    $body = $request->getBody()->getContents();

    $str = "$method $uri\n";
    foreach ($headers as $key => $values) {
      foreach ($values as $value) {
        $str .= "$key: $value\n";
      }
    }
    if ($body !== '') {
      $str .= "\n";
      $str .= "$body\n";
    }
    return $str;
  }

  /**
   * @param ServerRequestInterface $request
   * @return string
   * @noinspection PhpUnused
   */
  public static function getPlatformFromHeader(ServerRequestInterface $request):string {

    $platforms =   array(
      '/windows nt 10/i'      =>  'Windows 10',
      '/windows nt 6.3/i'     =>  'Windows 8.1',
      '/windows nt 6.2/i'     =>  'Windows 8',
      '/windows nt 6.1/i'     =>  'Windows 7',
      '/windows nt 6.0/i'     =>  'Windows Vista',
      '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
      '/windows nt 5.1/i'     =>  'Windows XP',
      '/windows xp/i'         =>  'Windows XP',
      '/windows nt 5.0/i'     =>  'Windows 2000',
      '/windows me/i'         =>  'Windows ME',
      '/win98/i'              =>  'Windows 98',
      '/win95/i'              =>  'Windows 95',
      '/win16/i'              =>  'Windows 3.11',
      '/macintosh|mac os x/i' =>  'Mac OS X',
      '/mac_powerpc/i'        =>  'Mac OS 9',
      '/linux/i'              =>  'Linux',
      '/ubuntu/i'             =>  'Ubuntu',
      '/iphone/i'             =>  'iPhone',
      '/ipod/i'               =>  'iPod',
      '/ipad/i'               =>  'iPad',
      '/android/i'            =>  'Android',
      '/blackberry/i'         =>  'BlackBerry',
      '/webos/i'              =>  'Mobile'
    );
    $platform = '';
    if ($request->hasHeader('User-Agent')) {
      $userAgent = $request->getHeader('User-Agent')[0];

      foreach ( $platforms as $regex => $value ) {
        if ( preg_match($regex, $userAgent ) ) {
          $platform = $value;
        }
      }
    }
    return $platform;
  }



  /// https://www.html-code-generator.com/php/get-user-details

  /**
   * @return string
   * @noinspection PhpUnused
   */
  public static function getBrowser(): string {
    $request = RequestFactory::fromGlobals();
    $browserName = "Unknown Browser";
    if ($request->hasHeader('User-Agent')) {
      $userAgent = $request->getHeader('User-Agent')[0];
      foreach (self::$browsers as $key => $val) {
        if (preg_match('|'.$key.'.|i', $userAgent, $match)) {
          $browserName = $val;
          break;
        }
      }
    }
    return $browserName;
  }

  /**
   * @return string
   * @noinspection PhpUnused
   */
  public static function getPlatform(): string {
    $request = RequestFactory::fromGlobals();
    $platform = "Unknown Platform";
    if ($request->hasHeader('User-Agent')) {
      $userAgent = $request->getHeader('User-Agent')[0];
      foreach (self::$os as $key => $val) {
        if (preg_match('|'.preg_quote($key).'|i', $userAgent)) {
          $platform = $val;
          break;
        }
      }
    }
    return $platform;
  }

  /**
   * @return string
   * @noinspection PhpUnused
   */
  public function getDeviceName(): string {
    $request = RequestFactory::fromGlobals();
    $mobile = "unknown";
    if ($request->hasHeader('User-Agent')) {
      $userAgent = $request->getHeader('User-Agent')[0];
      foreach (self::$device_name as $key => $val) {
        if (FALSE !== (stripos($userAgent, $key))) {
          $mobile = $val;
          break;
        }
      }
    }
    return $mobile;
  }

  /**
   * @return string
   * @noinspection PhpUnused
   */
  public function getDevice(): string {
    $tablet_browser = 0;
    $mobile_browser = 0;
    $userAgent = '';

    $request = RequestFactory::fromGlobals();
    if ($request->hasHeader('User-Agent')) {
      $userAgent = $request->getHeader('User-Agent')[0];
    }
    if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', strtolower($userAgent))) {
      $tablet_browser++;
    }
    if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', strtolower($userAgent))) {
      $mobile_browser++;
    }
    if ((strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml') > 0) or ((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))) {
      $mobile_browser++;
    }
    $mobile_ua = strtolower(substr($userAgent, 0, 4));
    $mobile_agents = array(
      'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',
      'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
      'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
      'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
      'newt','noki','palm','pana','pant','phil','play','port','prox',
      'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',
      'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',
      'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',
      'wapr','webc','winw','winw','xda ','xda-');
    if (in_array($mobile_ua,$mobile_agents)) {
      $mobile_browser++;
    }
    if (strpos(strtolower($userAgent),'opera mini') > 0) {
      $mobile_browser++;
      //Check for tablets on opera mini alternative headers
      $stock_ua = strtolower($_SERVER['HTTP_X_OPERAMINI_PHONE_UA'] ?? ($_SERVER['HTTP_DEVICE_STOCK_UA'] ?? ''));
      if (preg_match('/(tablet|ipad|playbook)|(android(?!.*mobile))/i', $stock_ua)) {
        $tablet_browser++;
      }
    }
    if ($tablet_browser > 0) {
      return 'Tablet';
    }
    else if ($mobile_browser > 0) {
      return 'Mobile';
    }
    else {
      return 'Desktop';
    }
  }

  /**
   * @return string
   * @noinspection PhpUnused
   */
  public function getIp(): string {
    /** @noinspection PhpTernaryExpressionCanBeReducedToShortVersionInspection */
    return (getenv('REMOTE_ADDR'))?getenv('REMOTE_ADDR'):'UNKNOWN';
  }
}
