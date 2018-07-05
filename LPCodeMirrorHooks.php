<?php

namespace Liquipedia\LPCodeMirror;

class Hooks {

	public static function onGetPreferences( $user, &$preferences ) {
		// CodeMirror settings
		$preferences[ 'lpcodemirror-prefs-use-codemirror-phone' ] = array(
			'type' => 'check',
			'label-message' => 'lpcodemirror-prefs-use-codemirror-phone',
			'section' => 'editing/lpcodemirror'
		);
		$preferences[ 'lpcodemirror-prefs-use-codemirror-tablet' ] = array(
			'type' => 'check',
			'label-message' => 'lpcodemirror-prefs-use-codemirror-tablet',
			'section' => 'editing/lpcodemirror'
		);
		$preferences[ 'lpcodemirror-prefs-use-codemirror' ] = array(
			'type' => 'check',
			'label-message' => 'lpcodemirror-prefs-use-codemirror',
			'section' => 'editing/lpcodemirror'
		);
		$preferences[ 'lpcodemirror-prefs-use-codemirror-linewrap' ] = array(
			'type' => 'check',
			'label-message' => 'lpcodemirror-prefs-use-codemirror-linewrap',
			'section' => 'editing/lpcodemirror'
		);
	}

	public static function onMakeGlobalVariablesScript( array &$vars, \OutputPage $out ) {
		$config = $out->getConfig();
		$context = $out->getContext();
		// add CodeMirror vars only for edit pages
		if ( in_array( $context->getRequest()->getText( 'action' ), array( 'edit', 'submit' ) ) ) {
			$parser = $config->get( 'Parser' );
			$contObj = $context->getLanguage();

			if ( !isset( $parser->mFunctionSynonyms ) ) {
				$parser->initialiseVariables();
				$parser->firstCallInit();
			}

			// initialize global vars
			$vars += array(
				'LPCodemirrorExtModes' => array(
					'tag' => array(
						'pre' => 'mw-tag-pre',
						'nowiki' => 'mw-tag-nowiki',
					),
					'func' => array(),
					'data' => array(),
				),
				'LPCodemirrorTags' => array_fill_keys( $parser->getTags(), true ),
				'LPCodemirrorDoubleUnderscore' => array( array(), array() ),
				'LPCodemirrorFunctionSynonyms' => $parser->mFunctionSynonyms,
				'LPCodemirrorUrlProtocols' => $parser->mUrlProtocols,
				'LPCodemirrorLinkTrailCharacters' => $contObj->linkTrail(),
			);

			$mw = $contObj->getMagicWords();
			foreach ( \MagicWord::getDoubleUnderscoreArray()->names as $name ) {
				if ( isset( $mw[ $name ] ) ) {
					$caseSensitive = array_shift( $mw[ $name ] ) == 0 ? 0 : 1;
					foreach ( $mw[ $name ] as $n ) {
						$vars[ 'LPCodemirrorDoubleUnderscore' ][ $caseSensitive ][ $caseSensitive ? $n : $contObj->lc( $n ) ] = $name;
					}
				} else {
					$vars[ 'LPCodemirrorDoubleUnderscore' ][ 0 ][] = $name;
				}
			}

			foreach ( \MagicWord::getVariableIDs() as $name ) {
				if ( isset( $mw[ $name ] ) ) {
					$caseSensitive = array_shift( $mw[ $name ] ) == 0 ? 0 : 1;
					foreach ( $mw[ $name ] as $n ) {
						$vars[ 'LPCodemirrorFunctionSynonyms' ][ $caseSensitive ][ $caseSensitive ? $n : $contObj->lc( $n ) ] = $name;
					}
				}
			}
		}
	}

	public static function onBeforePageDisplay( \OutputPage &$out, \Skin &$skin ) {
		if ( $skin->getUser()->getOption( 'lpcodemirror-prefs-use-codemirror' ) == true ) {
			$out->addModules( 'ext.LPCodeMirror.codemirror' );
		}
	}

	public static function onLoadExtensionSchemaUpdates( \DatabaseUpdater $updater ) {
		$updater->output( "\n" . 'Run updates for CodeMirror' . "\n" );
		$db = $updater->getDB();
		$preferences = [
			'prefs-use-codemirror-phone',
			'prefs-use-codemirror-tablet',
			'prefs-use-codemirror',
			'prefs-use-codemirror-linewrap',
		];
		foreach ( $preferences as $preference ) {
			$db->update( 'user_properties', [ 'up_property' => 'lpcodemirror-' . $preference ], [ 'up_property' => 'liquiflow-' . $preference ] );
		}
		$updater->output( "done.\n" );
		echo "\n";
	}

}
