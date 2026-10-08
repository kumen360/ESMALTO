<?php
/**
 * Lista de CNAE admitidos y validación.
 *
 * @package Esmalto_Core
 */

defined( 'ABSPATH' ) || exit;

class Esmalto_CNAE {

	/**
	 * Lista vigente: la de Ajustes si se ha editado, si no la inicial (data/cnae.php).
	 *
	 * @return array<string,string> código de 4 dígitos => descripción.
	 */
	public static function lista() {
		$texto = trim( (string) esmalto_ajuste( 'cnae' ) );
		if ( '' === $texto ) {
			return self::lista_inicial();
		}
		$lista = array();
		foreach ( preg_split( '/\r\n|\r|\n/', $texto ) as $linea ) {
			$linea = trim( $linea );
			if ( '' === $linea || '#' === $linea[0] ) {
				continue;
			}
			$partes = array_map( 'trim', explode( '|', $linea, 2 ) );
			$codigo = self::normalizar( $partes[0] );
			if ( '' !== $codigo ) {
				$lista[ $codigo ] = $partes[1] ?? '';
			}
		}
		return $lista;
	}

	public static function lista_inicial() {
		return include ESMALTO_CORE_DIR . 'data/cnae.php';
	}

	/**
	 * «43.33», «4333», «CNAE 4333» → «4333». Devuelve '' si no son 4 dígitos.
	 */
	public static function normalizar( $valor ) {
		$digitos = preg_replace( '/\D/', '', (string) $valor );
		return 4 === strlen( $digitos ) ? $digitos : '';
	}

	public static function es_valido( $valor ) {
		$codigo = self::normalizar( $valor );
		return '' !== $codigo && array_key_exists( $codigo, self::lista() );
	}

	public static function descripcion( $valor ) {
		$codigo = self::normalizar( $valor );
		$lista  = self::lista();
		return ( '' !== $codigo && isset( $lista[ $codigo ] ) ) ? $lista[ $codigo ] : '';
	}

	public static function formatear( $codigo ) {
		$codigo = (string) $codigo;
		return 4 === strlen( $codigo ) ? substr( $codigo, 0, 2 ) . '.' . substr( $codigo, 2 ) : $codigo;
	}

	/**
	 * Lista en formato de texto editable («4333 | Descripción» por línea).
	 */
	public static function como_texto( $lista = null ) {
		$lista  = null === $lista ? self::lista() : $lista;
		$lineas = array();
		foreach ( $lista as $codigo => $descripcion ) {
			$lineas[] = $codigo . ' | ' . $descripcion;
		}
		return implode( "\n", $lineas );
	}
}
