<?php
class BuildMenus
{

	public static function footer($dinoData, &$msg)
	{
		$html = '<div class="dino-footer">
	<div class="menu-menu-1-container">';
		$html .= self::getMenu() . self::footerCopyrightLine();
		$html .= "
			</div> <!-- close menu-menu-1-container -->
		</div> <!-- close dino-footer -->"; // close container
		return $html;
	} // end function footer
	public static function footerCopyrightLine()
	{
		global $eol;
		$msg = "";
		$msg .= "<span class='dino-footer-copyright'>copyright <a href='https://carnegiemnh.org/'>Carnegie Museum of Natural History</a> &nbsp;
					Hosted by the book <em><a href='https://freewheelingeasy.com/'>FreewheelingEasy in Western Pennsylvania</a></em> &nbsp;
					<a href='/feedback/'>Contact Us</a>
					</span>";;
		return $msg;
	} // end function header
	public static function getMenu()
	{
		$msg = "";
		$msg .= '
	<ul id="menu-menu-1" class="nav-menu menucolor">
		<li id="menu-item-508" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-508"><a href="https://dinomitedays.org/">Home</a></li>
		<li id="menu-item-652" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-has-children menu-item-652"><a href="https://dinomitedays.org/map/">Map</a>
			<ul class="sub-menu">
				<li id="menu-item-656" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-656"><a href="https://dinomitedays.org/known-locations/">Known Locations</a></li>
				<li id="menu-item-661" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-661"><a href="https://carnegiemnh.org/jurassic-days-dino-statue-driving-tour/">Driving Tour</a></li>
			</ul>
		</li>
		<li id="menu-item-516" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-has-children menu-item-516"><a href="https://dinomitedays.org/select-a-dinosaurer/">Select a dinosaur</a>
			<ul class="sub-menu">
				<li id="menu-item-640" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-640"><a href="https://dinomitedays.org/steg.htm">Stegosaurus</a></li>
				<li id="menu-item-642" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-642"><a href="https://dinomitedays.org/toro.htm">Torosaurus</a></li>
				<li id="menu-item-643" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-643"><a href="https://dinomitedays.org/rex.htm">Tyrannosaurus rex</a></li>
			</ul>
		</li>
		<li id="menu-item-660" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-660"><a href="https://dinomitedays.org/media.htm">Events</a>
			<ul class="sub-menu">
				<li id="menu-item-662" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-662"><a href="https://carnegiemnh.org/jurassic-days-dino-statue-driving-tour/">Driving Tour</a></li>
				<li id="menu-item-653" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-653"><a href="https://dinomitedays.org/gala.htm">Gala &#038; Live Action 2003/10/18</a></li>
				<li id="menu-item-655" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-655"><a href="https://dinomitedays.org/events.htm">Family Day 2003/10/19</a></li>
				<li id="menu-item-654" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-654"><a href="https://dinomitedays.org/online.htm">Dinomitedays Action</a></li>
				<li id="menu-item-658" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-658"><a href="https://dinomitedays.org/sold_price.htm">Action Prices</a></li>
			</ul>
		</li>
		<li id="menu-item-639" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-639"><a href="">Lists</a>
			<ul class="sub-menu">
				<li id="menu-item-510" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-510"><a href="https://dinomitedays.org/last_seen/">Last Seen</a></li>
				<li id="menu-item-646" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-646"><a href="https://dinomitedays.org/artist.htm">Artists</a></li>
				<li id="menu-item-509" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-509"><a href="https://dinomitedays.org/known-locations/">Known Locations</a></li>
				<li id="menu-item-645" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-645"><a href="https://dinomitedays.org/sponsor.htm">sponsors</a>
					<ul class="sub-menu">
						<li id="menu-item-647" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-647"><a href="https://dinomitedays.org/sponsorship.htm">Sponsorships</a></li>
						<li id="menu-item-657" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-657"><a href="https://dinomitedays.org/sold_price.htm">Auction Prices</a></li>
					</ul>
				</li>
				<li id="menu-item-659" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-659"><a href="https://dinomitedays.org/awards.htm">People Choice Awards</a></li>
				<li id="menu-item-659" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-659"><a href="https://dinomitedays.org/awards.htm">Website Awards</a></li>
			</ul>
		</li>
		<li id="menu-item-506" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-has-children menu-item-506"><a href="https://dinomitedays.org/hunters/">Dinosaur Hunters</a>
			<ul class="sub-menu">
				<li id="menu-item-514" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-514"><a href="https://dinomitedays.org/newsletter/">Newsletter</a></li>
				<li id="menu-item-663" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-663"><a href="https://dinomitedays.org/tracker/">Tracking History</a></li>
				<li id="menu-item-665" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-665"><a href="https://dinomitedays.org/build-dino-page/">Compare Page</a></li>
				<li id="menu-item-664" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-664"><a href="https://dinomitedays.org/info-update/">Modify Information Block</a></li>
				<li id="menu-item-666" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-666"><a href="https://dinomitedays.org/content/">Modify Page Content</a></li>
			</ul>
		</li>
		<li id="menu-item-517" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-517"><a href="https://dinomitedays.org/store/">Store</a></li>
		<li id="menu-item-520" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-520"><a href="https://administration">Administration</a>
			<ul class="sub-menu">
				<li id="menu-item-518" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-518"><a href="https://dinomitedays.org/upload/">Upload</a></li>
				<li id="menu-item-513" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-513"><a href="https://dinomitedays.org/tickets/">My Tickets</a></li>
				<li id="menu-item-515" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-515"><a href="https://dinomitedays.org/open-ticket/">Open Ticket</a></li>
				<li id="menu-item-142" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-142"><a href="https://dinomitedays.org/submit-ticket/">Submit Ticket</a></li>
			</ul>
		</li>
	</ul>
<!-- after the menu stuff, we close the body and html tags. -->
';
		return $msg;
	} // end function getMenu
} // end class BuildMenus