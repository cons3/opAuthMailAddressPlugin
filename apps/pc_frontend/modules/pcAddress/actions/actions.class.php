<?php

/**
 * pcAddress actions.
 *
 * @package    OpenPNE
 * @subpackage pcAddress
 * @author     Kousuke Ebihara <ebihara@tejimaya.com>
 * @version    SVN: $Id: actions.class.php 9301 2008-05-27 01:08:46Z dwhittle $
 */
class pcAddressActions extends sfActions
{
  public function executeRequestRegisterURL($request)
  {
    $this->form = new InviteForm();
    if ($request->isMethod('post'))
    {
      $this->form->bind($request->getParameter('member_config'));
      if ($this->form->isValid())
      {
        $this->form->save();

        return sfView::SUCCESS;
      }
    }

    return sfView::INPUT;
  }

  public function executeRegister($request)
  {
    $token = $request->getParameter('token');
    $memberConfig = MemberConfigPeer::retrieveByNameAndValue('pc_address_token', $token);
    $this->forward404Unless($memberConfig, 'This URL is invalid.');

    $this->getUser()->setMemberId($memberConfig->getMemberId());
    $this->getUser()->setIsSNSRegisterBegin(true);

    $this->redirect('member/registerInput');
  }

  public function executeRegisterEnd($request)
  {
    $member = $this->getUser()->getMember();
    $member->setIsActive(true);
    $member->save();

    $memberConfig = MemberConfigPeer::retrieveByNameAndMemberId('pc_address_token', $member->getId());
    $memberConfig->delete();

    $this->getUser()->setIsSNSMember(true);
    $this->redirect('member/home');
  }
}
