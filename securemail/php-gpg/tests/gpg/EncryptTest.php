<?php

/* ensure the framework libraries can be located */
set_include_path(
        realpath('../libs').
        PATH_SEPARATOR.get_include_path()
);

require_once 'PHPUnit/Framework/TestCase.php';
require_once 'GPG.php';

class EncryptTest extends PHPUnit_Framework_TestCase
{
    /**
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    public function setUp()
    {
    }

    /**
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    public function tearDown()
    {
    }

    /**
     * Return a public key used for encryption.
     *
     * @return string PGP public key
     */
    public function getTestKey()
    {
        return '-----BEGIN PGP PUBLIC KEY BLOCK-----
Version: GnuPG/MacGPG2 v2.0.22 (Darwin)
Comment: GPGTools - https://gpgtools.org

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
0w==
=Opd1
-----END PGP PUBLIC KEY BLOCK-----';
    }

    /**
     * Test that basic encryption returns a valid encrypted message.
     */
    public function test_Encrypt()
    {
        // jason's public key
        $public_key_ascii = $this->getTestKey();

        // plain text message
        $plain_text_string = "Whatever 90's tote bag, meggings put a bird on it cray bicycle rights vinyl semiotics Wes Anderson. Selvage Austin umami, letterpress Tumblr deep v kitsch polaroid. Trust fund messenger bag sartorial gluten-free, cred cray church-key pop-up Intelligentsia. Food truck Tumblr paleo mixtape XOXO banjo PBR&B Pinterest tofu banh mi. Portland messenger bag cornhole PBR Tonx High Life, DIY pork belly bespoke hoodie Terry Richardson dreamcatcher ethical forage. Put a bird on it slow-carb mixtape cardigan craft beer messenger bag. Aesthetic twee art party, Odd Future trust fund banjo ugh small batch semiotics.

Whatever asymmetrical keffiyeh literally narwhal. Keytar Odd Future blog, wayfarers literally gluten-free beard. Authentic Cosby sweater sustainable hashtag, VHS food truck kogi seitan put a bird on it YOLO. Selvage tousled mustache, flannel craft beer try-hard McSweeney's literally four loko YOLO keytar beard synth forage. Salvia Schlitz narwhal Terry Richardson typewriter, Wes Anderson butcher wolf. Slow-carb whatever bitters, letterpress trust fund pug before they sold out food truck artisan tousled. Church-key Vice craft beer Wes Anderson artisan flexitarian, kogi YOLO hella Tonx chia Neutra.

Farm-to-table actually Portland, artisan shabby chic vinyl organic seitan roof party distillery. Street art PBR&B banh mi, Tonx authentic you probably haven't heard of them fixie whatever tofu gluten-free. Gentrify locavore lo-fi umami, Thundercats salvia wolf four loko. Mixtape messenger bag gluten-free, squid American Apparel hella Shoreditch whatever selfies sriracha before they sold out. Pickled farm-to-table Intelligentsia occupy. Tumblr Etsy farm-to-table, mlkshk hella shabby chic meh jean shorts dreamcatcher fashion axe trust fund lomo Neutra. Freegan vegan narwhal tousled hoodie wolf flexitarian.

Flannel sriracha XOXO, slow-carb Godard ennui tousled American Apparel street art drinking vinegar lo-fi blog. Whatever Intelligentsia cardigan, Pinterest PBR&B pop-up semiotics. Jean shorts chillwave semiotics biodiesel. McSweeney's fap cardigan messenger bag fanny pack Cosby sweater Odd Future, Pitchfork four loko Marfa keytar mlkshk. 3 wolf moon McSweeney's gluten-free, umami freegan biodiesel fingerstache aesthetic sriracha swag Echo Park. Shabby chic selfies fixie, art party XOXO four loko chambray post-ironic letterpress messenger bag. Mustache beard lo-fi, flexitarian artisan tofu freegan occupy kale chips Carles twee chia bespoke.";

        $gpg = new GPG();
        $pub_key = new GPG_Public_Key($public_key_ascii);
        $encrypted = $gpg->encrypt($pub_key, $plain_text_string);

        $this->assertContains('-----BEGIN PGP MESSAGE-----', $encrypted, 'PGP Header Expected');

        $this->assertContains('-----END PGP MESSAGE-----', $encrypted, 'PGP Footer Expected');
    }
}
