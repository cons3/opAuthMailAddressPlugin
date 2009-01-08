<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

/**
 * opAuthMailAddress actions.
 *
 * @package    OpenPNE
 * @subpackage pcAddress
 * @author     Kousuke Ebihara <ebihara@tejimaya.com>
 * @version    SVN: $Id: actions.class.php 9301 2008-05-27 01:08:46Z dwhittle $
 */
class opAuthMailAddressActions extends sfActions
{
  public function executeRequestRegisterURL($request)
  {
    $adapter = new opAuthAdapterMailAddress('MailAddress');
    if ($adapter->getAuthConfig('invite_mode') < 2)
    {
      $this->forward404();
    }

    $this->forward404Unless(opToolkit::isEnabledRegistration());

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
    $this->getUser()->setCurrentAuthMode('MailAddress');

    $token = $request->getParameter('token');
    $memberConfig = MemberConfigPeer::retrieveByNameAndValue('pc_address_token', $token);
    $this->forward404Unless($memberConfig, 'This URL is invalid.');

    opActivateBehavior::disable();
    $authMode = $memberConfig->getMember()->getConfig('register_auth_mode');
    opActivateBehavior::enable();
    $this->forward404Unless($authMode === $this->getUser()->getCurrentAuthMode());

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
