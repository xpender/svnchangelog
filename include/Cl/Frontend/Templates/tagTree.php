<?php $this->display('header'); ?>

<div class="span10">
    <table class="table table-striped">
    <thead>
    <tr>
        <th>Revision</th>
        <th>Tag</th>
        <th>Branch</th>
    </tr>
    </thead>
    <tbody>
    <?php
    foreach ($this->get('aRevs') as $iRev => $aInfo) {
    ?>
    <tr>
        <td><?=$iRev?></td>
        <td><?=$aInfo['tag.name'] . '@' . $aInfo['tag.rev'];?></td>
        <td><?=$aInfo['branch.name'] . '@' . $aInfo['branch.rev'];?></td>
    </tr>
    <?php
    }
    ?>
    </tbody>
    </table>

</div>

<?php $this->display('footer'); ?>
