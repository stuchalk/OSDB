<footer class="footer">
    <div class="container">
        <p style="padding-top: 10px;">
            <?php
            $url='https://creativecommons.org/publicdomain/zero/1.0/';
            echo $this->Html->image('jsmol.png',['width'=>'100','url'=>'http://jmol.sourceforge.net/','alt'=>'JSmol','target'=>'_blank','style'=>'padding-right: 20px;']);
            echo $this->Html->image('cc-zero.png',['width'=>'100','url'=>$url,'alt'=>'License CC-Zero','target'=>'_blank','style'=>'padding-right: 20px;']);
            echo "Chalk Group @ ".$this->Html->link("University of North Florida",'http://www.unf.edu/',['target' =>'_blank'])." © 2015";
            ?>
        </p>
    </div>
</footer>