<?php $this->display('header'); ?>

<div class="span2">
    <div class="well sidebar-nav">
    <ul class="nav nav-list">
        <li class="nav-header">Tags</li>
        <?php
        foreach ($this->get('oDataTags')->all() as $aTag) {
            if ($this->get('sTag') == $aTag['tag']) {
        ?>
        <li class="active"><a href="/?action=changelog&project=<?=$this->get('sProject')?>&tag=<?=$aTag['tag'];?>"><?=$aTag['tag'];?></a></li>
        <?php
            } else {
        ?>
        <li><a href="/?action=changelog&project=<?=$this->get('sProject')?>&tag=<?=$aTag['tag'];?>"><?=$aTag['tag'];?></a></li>
        <?php
            }
        }
        ?>
    </ul>
    </div>
</div>

<div class="span10">
    <?php
    $oDataTagCommits = $this->get('oDataTagCommits');

    if (is_object($oDataTagCommits)) {
    ?>
    <table class="table table-striped">
    <thead>
    <tr>
        <th>Revision</th>
        <th>Merge</th>
        <th>Date</th>
        <th>Author</th>
        <th>Message</th>
    </tr>
    </thead>
    <tbody>
    <?php
        foreach (array_reverse($oDataTagCommits->all()) as $aCommit) {
    ?>
    <tr>
        <td><?=$aCommit['revision'];?></td>
        <td></td>
        <td><?=date('Y-m-d H:i:s', strtotime($aCommit['date']));?></td>
        <td><?=$aCommit['author'];?></td>
        <td><?=$aCommit['message'];?></td>
    </tr>
    <?php
            if (isset($aCommit['merges']) && is_array($aCommit['merges'])) {
                foreach ($aCommit['merges'] as $aMerge) {
    ?>
    <tr>
        <td></td>
        <td><?=$aMerge['revision'];?></td>
        <td><?=date('Y-m-d H:i:s', strtotime($aMerge['date']));?></td>
        <td><?=$aMerge['author'];?></td>
        <td><?=$aMerge['message'];?></td>
    </tr>
    <?php
                }
            }
        }
    ?>
    </tbody>
    </table>
    <?php
    }
    ?>
</div>

<?php $this->display('footer'); ?>
