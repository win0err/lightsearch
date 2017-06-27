<?php declare(strict_types=1);
/**
 * Copyright (c) 2017 Sergei Kolesnikov
 */

namespace win0err\LightSearch\MorphologyProcessor\Stemmer;


use win0err\LightSearch\MorphologyProcessor\MorphologyProcessorInterface;

/**
 * Class Russian
 *
 * Porter's russian stemmer
 * @see https://medium.com/@eigenein/%D1%81%D1%82%D0%B5%D0%BC%D0%BC%D0%B5%D1%80-%D0%BF%D0%BE%D1%80%D1%82%D0%B5%D1%80%D0%B0-%D0%B4%D0%BB%D1%8F-%D1%80%D1%83%D1%81%D1%81%D0%BA%D0%BE%D0%B3%D0%BE-%D1%8F%D0%B7%D1%8B%D0%BA%D0%B0-d41c38b2d340
 * @see http://forum.dklab.ru/php/advises/HeuristicWithoutTheDictionaryExtractionOfARootFromRussianWord.html
 * @see http://www.algorithmist.ru/2010/12/porter-stemmer-russian.html
 */
class PorterRussian implements MorphologyProcessorInterface {

	private static $cache = [];

	const VOWEL            = '/аеиоуыэюя/u';
	const PERFECTIVEGROUND = '/((ив|ивши|ившись|ыв|ывши|ывшись)|((?<=[ая])(в|вши|вшись)))$/u';
	const REFLEXIVE        = '/(с[яь])$/u';
	const ADJECTIVE        = '/(ее|ие|ые|ое|ими|ыми|ей|ий|ый|ой|ем|им|ым|ом|его|ого|ему|ому|их|ых|еых|ую|юю|ая|яя|ою|ею)$/u';
	const PARTICIPLE       = '/((ивш|ывш|ующ)|((?<=[ая])(ем|нн|вш|ющ|щ)))$/u';
	const VERB             = '/((ила|ыла|ена|ейте|уйте|ите|или|ыли|ей|уй|ил|ыл|им|ым|ен|ило|ыло|ено|ят|ует|уют|ит|ыт|ены|ить|ыть|ишь|ую|ю)|((?<=[ая])(ла|на|ете|йте|ли|й|л|ем|н|ло|но|ет|ют|ны|ть|ешь|нно)))$/u';
	const NOUN             = '/(а|ев|ов|ие|ье|е|иями|ями|ами|еи|ии|и|ией|ей|ой|ий|й|иям|ям|ием|ем|ам|ом|о|у|ах|иях|ях|ы|ь|у|ию|ью|ю|ия|ья|я)$/u';
	const RVRE             = '/^(.*?[аеиоуыэюя])(.*)$/u';
	const DERIVATIONAL     = '/[^аеиоуыэюя][аеиоуыэюя]+[^аеиоуыэюя]+[аеиоуыэюя].*(?<=о)сть?$/u';


	/**
	 * @var string[]
	 * @see http://snowballstem.org/algorithms/russian/stop.txt
	 */
	protected static $stopList = [
		'и'       => '',
		'или'     => '',
		'когда'   => '',
		'где'     => '',
		'куда'    => '',
		'если'    => '',
		'тире'    => '',
		'после'   => '',
		'перед'   => '',
		'менее'   => '',
		'более'   => '',
		'уж'      => '',
		'уже'     => '',
		'там'     => '',
		'тут'     => '',
		'туда'    => '',
		'сюдв'    => '',
		'оттуда'  => '',
		'отсюда'  => '',
		'здесь'   => '',
		'впрочем' => '',
		'зачем'   => '',
		'ничего'  => '',
		'никогда' => '',
		'иногда'  => '',
		'тогда'   => '',
		'всегда'  => '',
		'сейчас'  => '',
		'теперь'  => '',
		'сегодня' => '',
		'конечно' => '',
		'опять'   => '',
		'хоть'    => '',
		'хотя'    => '',
		'почти'   => '',
		'тоже'    => '',
		'также'   => '',
		'даже'    => '',
		'как'     => '',
		'так'     => '',
		'вот'     => '',
		'нет'     => '',
		'нету'    => 'нет',
		'вдруг'   => '',
		'через'   => '',
		'между'   => '',
		'еще'     => '',
		'ещё'     => 'еще',
		'чуть'    => '',
		'разве'   => '',
		'ведь'    => '',
		'нибудь'  => '',
		'будто'   => '',
		'можно'   => '',
		'нельзя'  => '',
		'хорошо'  => '',
		'только'  => '',
		'почему'  => '',
		'потому'  => '',
		'всего'   => '',
		'чтоб'    => '',
		'чтобы'   => 'чтоб',

		'под'  => '',
		'подо' => 'под',
		'об'   => '',
		'от'   => '',
		'без'  => '',
		'безо' => 'без',
		'над'  => '',
		'надо' => '',
		'из'   => '',

		'что'  => '',
		'чего' => 'что',
		'чему' => 'что',
		'чем'  => 'что',
		'чём'  => 'что',

		'кто'  => '',
		'кого' => 'кто',
		'кому' => 'кто',
		'кем'  => 'кто',
		'ком'  => 'кто',

		'шея'   => '',
		'шее'   => 'шея',
		'шеи'   => 'шея',
		'шеей'  => 'шея',
		'шей'   => 'шея',
		'шеями' => 'шея',
		'шеях'  => 'шея',

		'имя'     => '',
		'имени'   => 'имя',
		'именем'  => 'имя',
		'имена'   => 'имя',
		'именам'  => 'имя',
		'именами' => 'имя',
		'именах'  => 'имя',

		'она' => '',
		'её'  => 'она',
		'ее'  => 'она',
		'ей'  => 'она',
		'ней' => 'она',

		'один'   => '',
		'одного' => 'один',
		'одному' => 'один',
		'одним'  => 'один',
		'одном'  => 'один',

		'одна'  => '',
		'одной' => 'одна',
		'одну'  => 'одна',

		'он'  => '',
		'его' => 'он',
		'ему' => 'он',
		'ним' => 'он',
		//		'им'		=> 'он',
		'нем' => 'он',
		'нём' => 'он',

		'я'    => '',
		'меня' => 'я',
		'мне'  => 'я',
		'мной' => 'я',

		'ты'    => '',
		'тебя'  => 'ты',
		'тебе'  => 'ты',
		'тобой' => 'ты',

		'вас' => 'вы',
		'вам' => 'вы',

		'нас' => 'мы',
		'нам' => 'мы',

		'они'  => '',
		'их'   => 'они',
		'им'   => 'они',
		'ими'  => 'они',
		'ними' => 'они',
		'них'  => 'они',

		'ересь'  => 'ерес',
		'ереси'  => 'ерес',
		'ересью' => 'ерес',

		'ищу'   => 'иска',
		'ищешь' => 'иска',
		'ищет'  => 'иска',
		'ищем'  => 'иска',
		'ищете' => 'иска',
		'ищут'  => 'иска',
	];

	protected static function s(&$s, $re, $to) {

		$orig = $s;
		$s = preg_replace( $re, $to, $s );

		return $orig !== $s;
	}

	protected static function m($s, $re) {

		return preg_match( $re, $s );
	}

	/**
	 * {@inheritdoc}
	 */
	public static function apply(string $word): string {

		$word = mb_strtolower( $word );
		$word = str_replace( 'ё', 'е', $word );

		if (isset( self::$cache[$word] ))
			return self::$cache[$word];

		if (isset( self::$stopList[$word] ))
			return self::$stopList[$word] ? self::$stopList[$word] : $word;

		$stem = $word;
		do {

			if (!preg_match( self::RVRE, $word, $p ))
				break;


			$start = $p[1];
			$RV = $p[2];
			if (!$RV)
				break;


			// Step 1
			if (!self::s( $RV, self::PERFECTIVEGROUND, '' )) {
				self::s( $RV, self::REFLEXIVE, '' );

				if (self::s( $RV, self::ADJECTIVE, '' )) {
					self::s( $RV, self::PARTICIPLE, '' );
				} else {
					if (!self::s( $RV, self::VERB, '' )) {
						self::s( $RV, self::NOUN, '' );
					}
				}
			}

			// Step 2
			self::s( $RV, '/и$/u', '' );

			// Step 3
			if (self::m( $RV, self::DERIVATIONAL )) {
				self::s( $RV, '/ость?$/u', '' );
			}

			// Step 4
			if (!self::s( $RV, '/ь$/u', '' )) {
				self::s( $RV, '/ейше?/u', '' );
				self::s( $RV, '/нн$/u', 'н' );
			}

			$stem = $start . $RV;
		} while (false);

		self::$cache[$word] = $stem;

		return $stem;
	}
}