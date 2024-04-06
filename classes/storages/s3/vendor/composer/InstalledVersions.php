<?php


namespace Composer;

use Composer\Semver\VersionParser;


class InstalledVersions {
    private static $installed = array(
        'root' =>
            array(
                'pretty_version' => '1.0.0+no-version-set',
                'version' => '1.0.0.0',
                'aliases' =>
                    array(),
                'reference' => NULL,
                'name' => '__root__',
            ),
        'versions' =>
            array(
                '__root__' =>
                    array(
                        'pretty_version' => '1.0.0+no-version-set',
                        'version' => '1.0.0.0',
                        'aliases' =>
                            array(),
                        'reference' => NULL,
                    ),
                'aws/aws-crt-php' =>
                    array(
                        'pretty_version' => 'v1.2.4',
                        'version' => '1.2.4.0',
                        'aliases' =>
                            array(),
                        'reference' => 'eb0c6e4e142224a10b08f49ebf87f32611d162b2',
                    ),
                'aws/aws-sdk-php' =>
                    array(
                        'pretty_version' => '3.278.3',
                        'version' => '3.278.3.0',
                        'aliases' =>
                            array(),
                        'reference' => '596534c0627d8b38597061341e99b460437d1a16',
                    ),
                'guzzlehttp/guzzle' =>
                    array(
                        'pretty_version' => '6.5.8',
                        'version' => '6.5.8.0',
                        'aliases' =>
                            array(),
                        'reference' => 'a52f0440530b54fa079ce76e8c5d196a42cad981',
                    ),
                'guzzlehttp/promises' =>
                    array(
                        'pretty_version' => '1.5.3',
                        'version' => '1.5.3.0',
                        'aliases' =>
                            array(),
                        'reference' => '67ab6e18aaa14d753cc148911d273f6e6cb6721e',
                    ),
                'guzzlehttp/psr7' =>
                    array(
                        'pretty_version' => '1.9.1',
                        'version' => '1.9.1.0',
                        'aliases' =>
                            array(),
                        'reference' => 'e4490cabc77465aaee90b20cfc9a770f8c04be6b',
                    ),
                'mtdowling/jmespath.php' =>
                    array(
                        'pretty_version' => '2.6.1',
                        'version' => '2.6.1.0',
                        'aliases' =>
                            array(),
                        'reference' => '9b87907a81b87bc76d19a7fb2d61e61486ee9edb',
                    ),
                'psr/http-message' =>
                    array(
                        'pretty_version' => '1.0.1',
                        'version' => '1.0.1.0',
                        'aliases' =>
                            array(),
                        'reference' => 'f6561bf28d520154e4b0ec72be95418abe6d9363',
                    ),
                'psr/http-message-implementation' =>
                    array(
                        'provided' =>
                            array(
                                0 => '1.0',
                            ),
                    ),
                'ralouphie/getallheaders' =>
                    array(
                        'pretty_version' => '3.0.3',
                        'version' => '3.0.3.0',
                        'aliases' =>
                            array(),
                        'reference' => '120b605dfeb996808c31b6477290a714d356e822',
                    ),
                'symfony/polyfill-intl-idn' =>
                    array(
                        'pretty_version' => 'v1.29.0',
                        'version' => '1.29.0.0',
                        'aliases' =>
                            array(),
                        'reference' => 'a287ed7475f85bf6f61890146edbc932c0fff919',
                    ),
                'symfony/polyfill-intl-normalizer' =>
                    array(
                        'pretty_version' => 'v1.29.0',
                        'version' => '1.29.0.0',
                        'aliases' =>
                            array(),
                        'reference' => 'bc45c394692b948b4d383a08d7753968bed9a83d',
                    ),
                'symfony/polyfill-mbstring' =>
                    array(
                        'pretty_version' => 'v1.29.0',
                        'version' => '1.29.0.0',
                        'aliases' =>
                            array(),
                        'reference' => '9773676c8a1bb1f8d4340a62efe641cf76eda7ec',
                    ),
                'symfony/polyfill-php72' =>
                    array(
                        'pretty_version' => 'v1.29.0',
                        'version' => '1.29.0.0',
                        'aliases' =>
                            array(),
                        'reference' => '861391a8da9a04cbad2d232ddd9e4893220d6e25',
                    ),
            ),
    );


    public static function getInstalledPackages() {
        return array_keys(self::$installed['versions']);
    }


    public static function isInstalled($packageName) {
        return isset(self::$installed['versions'][$packageName]);
    }


    public static function satisfies(VersionParser $parser, $packageName, $constraint) {
        $constraint = $parser->parseConstraints($constraint);
        $provided = $parser->parseConstraints(self::getVersionRanges($packageName));

        return $provided->matches($constraint);
    }


    public static function getVersionRanges($packageName) {
        if (!isset(self::$installed['versions'][$packageName])) {
            throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
        }

        $ranges = array();
        if (isset(self::$installed['versions'][$packageName]['pretty_version'])) {
            $ranges[] = self::$installed['versions'][$packageName]['pretty_version'];
        }
        if (array_key_exists('aliases', self::$installed['versions'][$packageName])) {
            $ranges = array_merge($ranges, self::$installed['versions'][$packageName]['aliases']);
        }
        if (array_key_exists('replaced', self::$installed['versions'][$packageName])) {
            $ranges = array_merge($ranges, self::$installed['versions'][$packageName]['replaced']);
        }
        if (array_key_exists('provided', self::$installed['versions'][$packageName])) {
            $ranges = array_merge($ranges, self::$installed['versions'][$packageName]['provided']);
        }

        return implode(' || ', $ranges);
    }


    public static function getVersion($packageName) {
        if (!isset(self::$installed['versions'][$packageName])) {
            throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
        }

        if (!isset(self::$installed['versions'][$packageName]['version'])) {
            return null;
        }

        return self::$installed['versions'][$packageName]['version'];
    }


    public static function getPrettyVersion($packageName) {
        if (!isset(self::$installed['versions'][$packageName])) {
            throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
        }

        if (!isset(self::$installed['versions'][$packageName]['pretty_version'])) {
            return null;
        }

        return self::$installed['versions'][$packageName]['pretty_version'];
    }


    public static function getReference($packageName) {
        if (!isset(self::$installed['versions'][$packageName])) {
            throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
        }

        if (!isset(self::$installed['versions'][$packageName]['reference'])) {
            return null;
        }

        return self::$installed['versions'][$packageName]['reference'];
    }


    public static function getRootPackage() {
        return self::$installed['root'];
    }


    public static function getRawData() {
        return self::$installed;
    }


    public static function reload($data) {
        self::$installed = $data;
    }
}
