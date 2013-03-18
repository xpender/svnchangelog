<?php
/**
 * svnchangelog
 *
 * @package net.xpender.svnchangelog
 * @author Marko Kercmar <m.kercmar@bigpoint.net>
 */
class Cl_Frontend_Action_Changelog extends Cl_Frontend_ActionAbstract
{
    public function getName()
    {
        return 'changelog';
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

        // tag set and valid?
        $sTag = false;

        if (isset($_REQUEST['tag']) && $oDataTags->exists($_REQUEST['tag'])) {
            $sTag = $_REQUEST['tag'];
        }

        // if tag.. get commits
        $oDataTagCommits = false;

        if ($sTag) {
            $oDataTagCommits = new Cl_Data_TagCommits(
                $sProject,
                $sTag
                );
        }

        // assign to template
        $this->_oTemplate->assign('sProject', $sProject);
        $this->_oTemplate->assign('sTag', $sTag);
        $this->_oTemplate->assign('oDataTags', $oDataTags);
        $this->_oTemplate->assign('oDataTagCommits', $oDataTagCommits);

        // show template
        $this->_oTemplate->display(
            'changelog'
            );
    }
}
