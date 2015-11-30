<h2>The OSDB API</h2>

<p class="text-justify">While we expect a lot of humans to stop by the OSDB, we are very friendly towards computers and have set up an
application programming interface (API) so they may download a bunch of stuff.  Here is the overview of the API
and we are working on additional documentation.</p>

<!-- Spectra -->
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">Spectra</h3>
            </div>
            <div class="panel-body">
                <table class="table table-condensed table-striped">
                    <tr>
                        <th class="col-md-4">Endpoint</th>
                        <th class="col-md-5">Notes</th>
                        <th class="col-md-3">Example(s)</th>
                    </tr>
                    <tr>
                        <td><code>/spectra/view/[osdbid]</code></td>
                        <td>Spectrum using id</td>
                        <td><?php echo $this->Html->link('/spectra/view/11','/spectra/view/11'); ?></td>
                    </tr>
                    <tr>
                        <td><code>/spectra/view/[comp]/[tech]/[format]</code></td>
                        <td>Spectrum via compound identifer and technique code<br />
                            [comp]: name, cas#, inchi, inchikey, smiles<br />
                            [tech]: MS, IR, 1HNMR, 13CNMR<br />
                            [format]: (HTML), JCAMP, XML, JSONLD</td>
                        <td>
                            <?php echo $this->Html->link('/spectra/view/1,4-dibromobenzene/MS','/spectra/view/1,4-dibromobenzene/MS'); ?>
                            <?php echo $this->Html->link('/spectra/view/1-aminopropane/MS/XML','/spectra/view/1-aminopropane/MS/XML'); ?>
                        </td>
                    </tr>
                    <tr>
                        <td><code>/spectra/plot/[osdbid]</code></td>
                        <td>Spectrum plot only using id</td>
                        <td><?php echo $this->Html->link('/spectra/plot/11','/spectra/plot/11'); ?></td>
                    </tr>
                    <tr>
                        <td><code>/spectra/plot/[comp]/[tech]/[width][height]/[embed]</code></td>
                        <td>Spectrum plot only via compound identifer and technique code<br />
                            [comp]: name, cas#, inchi, inchikey, smiles<br />
                            [tech]: MS, IR, 1HNMR, 13CNMR<br />
                        </td>
                        <td>
                            <?php echo $this->Html->link('/spectra/plot/1,4-dibromobenzene/MS','/spectra/plot/1,4-dibromobenzene/MS'); ?>
                            <?php echo $this->Html->link('/spectra/plot/106-37-6/MS/400/300/embed','/spectra/plot/106-37-6/MS/400/300/embed'); ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Substances -->
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-success">
            <div class="panel-heading">
                <h3 class="panel-title">Substances</h3>
            </div>
            <div class="panel-body">
                <table class="table table-condensed table-striped">
                    <tr>
                        <th class="col-md-4">Endpoint</th>
                        <th class="col-md-5">Notes</th>
                        <th class="col-md-3">Example(s)</th>
                    </tr>
                    <tr>
                        <td><code>/substances/index/[format]</code></td>
                        <td>List of substances<br />
                            [format]: (HTML), XML, JSON
                        </td>
                        <td>
                            <?php echo $this->Html->link('/substances','/substances'); ?>
                            <?php echo $this->Html->link('/substances/index/XML','/substances/index/XML'); ?>
                        </td>
                    </tr>
                    <tr>
                        <td><code>/substances/view/[osdbid]/[format]</code></td>
                        <td>Substance using id<br />
                            [format]: (HTML), XML, JSON
                        </td>
                        <td><?php echo $this->Html->link('/substances/view/11','/substances/view/11'); ?></td>
                    </tr>
                    <tr>
                        <td><code>/substances/view/[comp]/[format]</code></td>
                        <td>Spectrum via compound identifer and technique code<br />
                            [comp]: name, cas#, inchi, inchikey, smiles<br />
                            [format]: (HTML), XML, JSON</td>
                        <td>
                            <?php echo $this->Html->link('/substances/view/1,4-dibromobenzene','/substances/view/1,4-dibromobenzene'); ?>
                            <?php echo $this->Html->link('/substances/view/1-aminopropane/JSON','/substances/view/1-aminopropane/JSON'); ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Collections -->
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-info">
            <div class="panel-heading">
                <h3 class="panel-title">Collections</h3>
            </div>
            <div class="panel-body">
                <table class="table table-condensed table-striped">
                    <tr>
                        <th class="col-md-4">Endpoint</th>
                        <th class="col-md-5">Notes</th>
                        <th class="col-md-3">Example</th>
                    </tr>
                    <tr>
                        <td><code>/collections/view/[osdbid]/[format]</code></td>
                        <td>Collection using id<br />
                            [format]: (HTML), XML, JSON
                        </td>
                        <td><?php echo $this->Html->link('/collections/view/1','/collections/view/1'); ?></td>
                    </tr>
                    <tr>
                        <td><code>/collections/view/[colname]/[format]</code></td>
                        <td>Collection using name<br />
                            format]: (HTML), XML, JSON</td>
                        <td>
                            <?php echo $this->Html->link('/collections/view/SpectraSchool','/collections/view/SpectraSchool'); ?>
                            <br /> - OR- <br />
                            <?php echo $this->Html->link('/collections/view/SpectraSchool/JSON','/collections/view/SpectraSchool/JSON'); ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>