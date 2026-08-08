<?php
class homeOriginal
{
    public static function display(array $attributes)
    {
        global $wpdbExtra;
        $msg = "";
        $msg .= '<base href="https://dinomitedays.org/" />';
        $msg .= '
        <script type="text/javascript">
        var titleTop = document.getElementsByClassName("site-title");
            titleTop[0].style.lineHeight = "normal";
           titleTop[0].innerHTML = titleTop[0].innerHTML.replace("Carnegie Museum of Natural History and the City of Pittsburgh", "Dinomitedays");
           titleTop[0].innerHTML = titleTop[0].innerHTML.replace("– Pittsburgh Dinosaurs – Dinomitedays", "");
        var titleLow = document.getElementsByClassName("entry-title");
           // titleLow[0].style.display = "none";
        </script>
        <style>
        <style>
  .flex-container {
    display: flex;
    justify-content: space-between;
    gap: 20px;
  }
  .flex-col {
    flex: 1;
  }
</style>

<div class="flex-container">
    <div class="flex-col">
        <div class="flex-container">
            <div class="flex-col">
                <img src="graphics/head_block_left.gif" width="234" height="128" align=right hspace=0 vspace=0 alt="Geniusaurus">
                <font size="2" face="Arial, Helvetica, sans-serif">We are
                    proud to present DinoMite Days<sup>SM</sup>,
                    an event that turned back the clock
                    to the Age of Dinosaurs!</font>
                <br />
               <font size="2" face="Arial, Helvetica, sans-serif">
                    This event celebrated Carnegie Museum of
                    Natural History\'s reputation for scientific
                    excellence, while showcasing the talents
                    of established and emerging artists.</font>
            </div>
        </div>
    </div>
</div>
<div class="flex-left">
    <div class="flex-col">
                <!-- image is tthe "Acton Results" -->
                <img src="graphics/results.gif" width="181" height="29" alt="Hug one!"><br>
                <font color="#CC0000" size="3" face="Arial, Helvetica, sans-serif"><b>
                        <font color="#003399" size="2">The
                            stampede is over!&nbsp; Check
                            the status </font>
                        <font color="#003399" size="2">of
                            your favorite dinos: <br>
                        </font>
                    </b></font><b><a href="sold_price.htm">
                        <font color="#FF9900" size="2" face="Arial, Helvetica, sans-serif">By
                            Price</font>
                    </a></b> | <a href="sold_dino.htm">
                    <font color="#FF9900" size="2" face="Arial, Helvetica, sans-serif"><b>By
                            Dino</b></font>
                </a> | <b><a href="sold_lot.htm">
                        <font color="#FF9900" size="2" face="Arial, Helvetica, sans-serif"><b>By
                                Lot</b></font>
                    </a></b><br>
                <b><a href="sold_sponsor.htm">
                        <font color="#FF9900" size="2" face="Arial, Helvetica, sans-serif">By
                            Sponsor</font>
                    </a></b> | <a href="sold_artist.htm">
                    <font color="#FF9900" size="2" face="Arial, Helvetica, sans-serif"><b>By
                            Artist</b></font>
                </a><br>
                <a href="/last_seen/">
                    <font color="#FF9900" size="2" face="Arial, Helvetica, sans-serif"><b>By
                            Last seen date</b></font>
                </a><a href="/last_seen/?lastorkey=key">
                    <font color="#FF9900" size="2" face="Arial, Helvetica, sans-serif"><b>.
                </a> |
                <a href="/map/">
                    <font color="#FF9900" size="2" face="Arial, Helvetica, sans-serif"><b>By Map</b></font>
                </a><br>
                <font color="#009900" size="2" face="Arial, Helvetica, sans-serif"><b>If
                        you are a dinosaur owner, please</b></font><br>
                <a href="/online.htm">
                    <font color="#009900" size="2" face="Arial, Helvetica, sans-serif">
                    <b>click for repair information</b></font>
                </a>
                <br>
            </div>
            <div class="flex-col">
             <font color="#003399" size="2" face="Arial, Helvetica, sans-serif">
                    <b>Search by picture !</b>
                    </font>
    ';
        $sqlAll = dinomitedays_misc_pages::dinoSglSelect . " $wpdbExtra->dinosaurs ORDER BY dinoName";
        $msg .= dinomitedays_misc_pages::gridDisplay($sqlAll, "nameOnly");
        $msg  .= '

            </div>
            <div class="flex-col">
                        <img src="transparent.gif" width="10"></td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" valign=top background="graphics/white.gif">
                                                <div align="center">
                                                    <p align="left">
                                                        <font color="#CC0000" size="3" face="Arial, Helvetica, sans-serif">
                                                            <a href="/designs/childhosp.htm">
                                                                           <b><img src="graphics/sq_r_T.%20reX%20Ray.jpg" alt="T. reX Ray" width="120" height="110" border="0" align="left"></b>
                                                            </a>
                                                            <font color="#000000" size="2">&quot;TIME
                                                                MARCHES ON, so it\'s only natural
                                                                that Pittsburgh\'s age of the
                                                                dinosaurs would come to an
                                                                end. They were not real dinosaurs
                                                                &#8212; not like the county
                                                                row offices &#8212; but the
                                                                fanciful re-creations of dinosaur-inspired
                                                                artists. For four months,
                                                                the herd of 100 DinoMite Days
                                                                dinosaurs brightened many
                                                                parts of the region as </font>
                                                            <font color="#CC0000" size="3" face="Arial, Helvetica, sans-serif"><b><a href="/sold_lot.htm"><img src="graphics/rex_sm.gif" alt="Amazing Hands" width="66" height="80" border=0 align="right"></a></b></font>
                                                            <font color="#000000" size="2">a
                                                                fund-raising venture for Carnegie
                                                                Museum of Natural History,
                                                                which, of course, is renowned
                                                                for its dinosaur bone collection.
                                                                The dinosaurs have been auctioned
                                                                off to raise money for renovation
                                                                of the museum\'s Dinosaur Hall,
                                                                as well as charities....Pittsburgh
                                                                will miss these wonderful
                                                                creatures. Indeed, nothing
                                                                like it has been seen around
                                                                here since prehistoric times.&quot;
                                                            </font>
                                                        </font>
                                                    </p>
                                                    <p align="right">
                                                        <font color="#000000" size="2" face="Arial, Helvetica, sans-serif">&#8212;
                                                            <i>Pittsburgh Post-Gazette</i>,
                                                            Sunday, November 02, 2003
                                                        </font>
                                                    </p>
            </div>
        </div>
    <div class="flex-container">
            <div class="flex-col">
                                    <img src="fun/dinonames/graphics/trex19.gif" width="400" height="40">
            </div>
        </div>
    </div>
        ';
        return $msg;
    } //
} // end class