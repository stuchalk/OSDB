<div class="row">
    <div class="col-md-6 col-md-offset-3 text-justify">
        <h2>About the OSDB</h2>
        The OSDB came out of a conversation (and beer) with Tony Williams (of ChemSpider fame) where he commented that
        we chemistry needed a website where people could share their spectral data.  Somehow I can't resist a challenge
        and with the upcoming session honoring 'J.C. Bradley' at the ACS meeting in Boston (August 2015) I decided to
        build a site to share spectral data and also make the site open in terms of access, availability of code and
        developers. Although I gave a presentation at the meeting on the alpha version of this site, feedback from Tony
        made me rethink the design and implementation significantly and so this beta version is a drastic improvement.<br>&nbsp;
    </div>
</div>
<div class="row">
    <div class="col-md-6 col-md-offset-3 text-justify">
        The technology behind the site is the pretty standard stack of Apache, PHP, and MySQL.  The coding is done in
        the indispensible <?php echo $this->Html->link('PHPStorm','https://www.jetbrains.com/',['target'=>'_blank']); ?>
        (available for free to academics :) ) which is integrated into the GitHub repo for the site and configured to
        automatically upload changes to where the site is hosted. This allows me to have a local copy for testing
        and this public site.  Although PHPStorm can integrate with MySQL, I choose to admin the MySQL database using
        PHPMyAdmin which I highly recommend.  Finally, the look and feel and a lot of the functionality are done
        using Bootstrap and jQuery, with the addition of flot for doing the spectral plots and Jmol for the molecules.
        <br>&nbsp;
    </div>
</div>
<div class="row">
    <div class="col-md-6 col-md-offset-3 text-justify">
        <i>Stuart Chalk - December 2015</i><br>&nbsp;
    </div>
</div>
<div class="row">
    <?php echo $this->element('contact'); ?>
</div>