{*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div id="custom-text">
{if $homeslider.slides}
  <div id="carousel" data-ride="carousel" class="carousel slide hidden-sm-down" data-interval="{$homeslider.speed}" data-wrap="{(string)$homeslider.wrap}" data-pause="{$homeslider.pause}">
    <ul class="carousel-inner" role="listbox">
      {foreach from=$homeslider.hotspotimages  item=slide name='homeslider'}
        <li class="carousel-item {if $smarty.foreach.homeslider.first}active{/if}">
          <figure>
            <img src="{$homeslider.image_baseurl}{$slide.image}" alt="">  
             <div class="mrker-overlay">
            {foreach from=$homeslider.slides item=slidess name='homeslider'}
	           
	         		<div class="jTagTag" id="tag2" style="width: {$slidess.width}px; height: {$slidess.height}px; top: {$slidess.top}px; left: {$slidess.left}px; opacity: 1;">
	         			<a href="{$slidess.cat_url}">
	         				<img src="http://testows.it/shops/modules/ps_hotspot/images/mapping.png">
	         			</a>
	         		</div>
	         	
         	{/foreach}	
         	</div>
          </figure>
        </li>
      {/foreach}
    </ul>
    <div class="direction">
      <a class="left carousel-control" href="#carousel" role="button" data-slide="prev">
        <span class="icon-prev hidden-xs" aria-hidden="true">
          <i class="material-icons">&#xE5CB;</i>
        </span>
        <span class="sr-only">Previous</span>
      </a>
      <a class="right carousel-control" href="#carousel" role="button" data-slide="next">
        <span class="icon-next" aria-hidden="true">
          <i class="material-icons">&#xE5CC;</i>
        </span>
        <span class="sr-only">Next</span>
      </a>
    </div>
  </div>
{/if}
</div>

<style type="text/css">
	
.carousel.slide .mrker-overlay .jTagTag img {
    max-width: 100%;
    width: auto;
}
.mrker-overlay .jTagTag {
    position: absolute;
    text-align: left;
}
.carousel.slide .direction a.carousel-control {
    width: 5%;
}
.carousel.slide img {
    width: 100%;
}
div#carousel {
    width: 502px;
    height: 502px;
}
.mrker-overlay {
    width: 100%;
/*    position: relative;*/
    z-index: 99999999;
}



</style>
