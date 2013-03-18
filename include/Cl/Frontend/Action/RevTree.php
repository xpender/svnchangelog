<?php
/**
 * svnchangelog
 *
 * @package net.xpender.svnchangelog
 * @author Marko Kercmar <m.kercmar@bigpoint.net>
 */
class Cl_Frontend_Action_RevTree extends Cl_Frontend_ActionAbstract
{
    public function getName()
    {
        return 'revTree';
    }

    protected function _execute()
    {
        // project via session
        if (!Cl_Frontend_Session::getProject()) {
            Header('Location: /');
            exit;
        }

        // get project
        $sProject = Cl_Frontend_Session::getProject();

        // get tags..
        $oDataTags = new Cl_Data_Tags(
            $sProject
            );

        // get branches..
        $oDataBranches = new Cl_Data_Branches(
            $sProject
            );

        // le rev scale
        $aRevs = array();

        foreach ($oDataTags->all() as $aTag) {
            $aRevs[$aTag['tag.rev']] = array(
                'kind' => 'tag',
                'tag.name' => $aTag['tag.name'],
                'tag.rev' => $aTag['tag.rev'],
                'branch.name' => $aTag['branch.name'],
                'branch.rev' => $aTag['branch.rev']
                );
        }

        foreach ($oDataBranches->all() as $aBranch) {
            $aRevs[$aBranch['rev.create']] = array(
                'kind' => 'branch',
                'branch.name' => $aBranch['name']
                );
        }

        krsort(
            $aRevs,
            SORT_NUMERIC
            );
        
        // assign to template
        $this->_oTemplate->assign('sProject', $sProject);
        $this->_oTemplate->assign('aRevs', $aRevs);

        // show template
        $this->_oTemplate->display(
            'revTree'
            );
    }
}
