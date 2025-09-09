{**
 * Blocks Categories on HomePage: module for PrestaShop.
 *
 * @author    profilweb. <manu@profil-web.fr>
 * @copyright 2021 profil Web.
 * @link      https://github.com/profilweb/pw_homecategories The module's homepage
 * @license   https://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 *}

<!-- MODULE pw_bandeau -->
{if !empty($bandeau_text)}
    <div id="bandeau" class="bandeau">
        <div class="txt-defilant">
            <p>
                <span>{$bandeau_text}</span>
                <span>{$bandeau_text}</span>
            </p>
        </div>
    </div><!-- .bandeau -->
{/if}
<!-- /MODULE pw_bandeau -->