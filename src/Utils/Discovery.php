<?php
namespace Fkwd\Plugin\Wcrfc\Utils;

class Discovery
{    
    public static function discover( $directory, $namespace, $features_namespace )
    {
        $features = [];

        foreach ( glob($directory . '/*.php') as $file ) {
            $className = $namespace . $features_namespace . basename( $file, '.php' );

            if ( ! class_exists( $className ) ) {
                continue;
            }

            $ref = new \ReflectionClass( $className );
            if ( $ref->isAbstract() || $ref->isInterface() ) {
                continue;
            }

            if ( $ref->implementsInterface( $namespace . 'Interface\\Feature' ) ) {
                $features[] = $ref->newInstance();
            }
        }

        return $features;
    }
}
