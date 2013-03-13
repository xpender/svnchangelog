<?php $this->display('header'); ?>

<div class="span2">
    <div class="well sidebar-nav">
    <ul class="nav nav-list">
        <li class="nav-header">Tags</li>
        <?php
        $aAllTags = $this->get('oDataTags')->all();

        function sortByRev($a, $b) {
            if ($a['revision.scm'] > $b['revision.scm']) {
                return -1;
            } elseif ($a['revision.scm'] < $b['revision.scm']) {
                return 1;
            }

            return 0;
        }

        uasort($aAllTags, 'sortByRev');

        foreach ($aAllTags as $aTag) {
            if ($this->get('sTag') == $aTag['tag']) {
        ?>
        <li class="active"><a href="/?action=changelog&project=<?=$this->get('sProject')?>&tag=<?=$aTag['tag'];?>"><?=$aTag['tag'];?></a></li>
        <?php
            } elseif ($aTag['revision.local'] == 0) {
        ?>
        <li><?=$aTag['tag'];?></li>
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
    $aAllCommits = $oDataTagCommits->all();
    krsort($aAllCommits);

        foreach ($aAllCommits as $aCommit) {
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
