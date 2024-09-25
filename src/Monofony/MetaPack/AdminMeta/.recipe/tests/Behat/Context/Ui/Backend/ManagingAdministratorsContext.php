<?php

declare(strict_types=1);

namespace App\Tests\Behat\Context\Ui\Backend;

use App\Tests\Behat\Element\Backend\TopBarElement;
use App\Tests\Behat\Page\Backend\Administrator\CreatePage;
use App\Tests\Behat\Page\Backend\Administrator\IndexPage;
use App\Tests\Behat\Page\Backend\Administrator\UpdatePage;
use Behat\Behat\Context\Context;
use Doctrine\Persistence\ObjectManager;
use Monofony\Bridge\Behat\NotificationType;
use Monofony\Bridge\Behat\Service\NotificationCheckerInterface;
use Monofony\Bridge\Behat\Service\SharedStorageInterface;
use Monofony\Contracts\Core\Model\User\AdminUserInterface;
use Webmozart\Assert\Assert;
use Zenstruck\Foundry\Persistence\Proxy;

final class ManagingAdministratorsContext implements Context
{
    public function __construct(
        private CreatePage $createPage,
        private IndexPage $indexPage,
        private UpdatePage $updatePage,
        private TopBarElement $topBarElement,
        private NotificationCheckerInterface $notificationChecker,
        private SharedStorageInterface $sharedStorage,
        private ObjectManager $objectManager,
    ) {
    }

    /**
     * @Given I want to create a new administrator
     */
    public function iWantToCreateANewAdministrator(): void
    {
        $this->createPage->open();
    }

    /**
     * @When /^I am editing (my) details$/
     * @When /^I want to edit (this administrator)$/
     */
    public function iWantToEditThisAdministrator(AdminUserInterface $adminUser): void
    {
        $this->updatePage->open(['id' => $adminUser->getId()]);
    }

    /**
     * @When I browse administrators
     * @When I want to browse administrators
     */
    public function iWantToBrowseAdministrators(): void
    {
        $this->indexPage->open();
    }

    /**
     * @When I specify its name as :username
     * @When I do not specify its name
     */
    public function iSpecifyItsNameAs(string $username = null): void
    {
        $this->createPage->specifyUsername($username);
    }

    /**
     * @When I change its name to :username
     */
    public function iChangeItsNameTo(string $username): void
    {
        $this->updatePage->changeUsername($username);
    }

    /**
     * @When I specify its email as :email
     * @When I do not specify its email
     */
    public function iSpecifyItsEmailAs(string $email = null): void
    {
        $this->createPage->specifyEmail($email);
    }

    /**
     * @When I change its email to :email
     */
    public function iChangeItsEmailTo(string $email): void
    {
        $this->updatePage->changeEmail($email);
    }

    /**
     * @When I specify its password as :password
     * @When I do not specify its password
     */
    public function iSpecifyItsPasswordAs(string $password = null): void
    {
        $this->createPage->specifyPassword($password);
    }

    /**
     * @When I change its password to :password
     */
    public function iChangeItsPasswordTo(string $password): void
    {
        $this->updatePage->changePassword($password);
    }

    /**
     * @When I enable it
     */
    public function iEnableIt(): void
    {
        $this->createPage->enable();
    }

    /**
     * @When I (try to) add it
     */
    public function iAddIt(): void
    {
        $this->createPage->create();
    }

    /**
     * @When I save my changes
     */
    public function iSaveMyChanges(): void
    {
        $this->updatePage->saveChanges();
    }

    /**
     * @When I delete administrator with email :email
     */
    public function iDeleteAdministratorWithEmail(string $email): void
    {
        $this->indexPage->deleteResourceOnPage(['email' => $email]);
    }

    /**
     * @When I check (also) the :email administrator
     */
    public function iCheckTheAdministrator(string $email): void
    {
        $this->indexPage->checkResourceOnPage(['email' => $email]);
    }

    /**
     * @When I delete them
     */
    public function iDeleteThem(): void
    {
        $this->indexPage->bulkDelete();
    }

    /**
     * @When /^I (?:|upload|update) the "([^"]+)" image as (my) avatar$/
     */
    public function iUploadTheImageAsMyAvatar(string $avatar, AdminUserInterface|Proxy $administrator): void
    {
        $path = $this->updateAvatar($avatar, $administrator);

        $this->sharedStorage->set($avatar, $path);
    }

    /**
     * @Then the administrator :email should appear in the list
     * @Then I should see the administrator :email in the list
     * @Then there should still be only one administrator with an email :email
     */
    public function theAdministratorShouldAppearInTheList(string $email): void
    {
        $this->indexPage->open();

        Assert::true($this->indexPage->isSingleResourceOnPage(['email' => $email]));
    }

    /**
     * @Then this administrator with name :username should appear in the list
     * @Then there should still be only one administrator with name :username
     */
    public function thisAdministratorWithNameShouldAppearInTheList(string $username): void
    {
        $this->indexPage->open();

        Assert::true($this->indexPage->isSingleResourceOnPage(['username' => $username]));
    }

    /**
     * @Then I should see a single administrator in the list
     * @Then there should be :amount administrators in the list
     */
    public function iShouldSeeAdministratorsInTheList(int $amount = 1): void
    {
        Assert::same($this->indexPage->countItems(), $amount);
    }

    /**
     * @Then I should be notified that email must be unique
     */
    public function iShouldBeNotifiedThatEmailMustBeUnique(): void
    {
        Assert::same($this->createPage->getValidationMessage('email'), 'This email is already used.');
    }

    /**
     * @Then I should be notified that name must be unique
     */
    public function iShouldBeNotifiedThatNameMustBeUnique(): void
    {
        Assert::same($this->createPage->getValidationMessage('name'), 'This username is already used.');
    }

    /**
     * @Then I should be notified that the :elementName is required
     */
    public function iShouldBeNotifiedThatFirstNameIsRequired(string $elementName): void
    {
        Assert::same($this->createPage->getValidationMessage($elementName), sprintf('Please enter your %s.', $elementName));
    }

    /**
     * @Then I should be notified that this email is not valid
     */
    public function iShouldBeNotifiedThatEmailIsNotValid(): void
    {
        Assert::same($this->createPage->getValidationMessage('email'), 'This email is invalid.');
    }

    /**
     * @Then this administrator should not be added
     */
    public function thisAdministratorShouldNotBeAdded(): void
    {
        $this->indexPage->open();

        Assert::same($this->indexPage->countItems(), 1);
    }

    /**
     * @Then there should not be :email administrator anymore
     */
    public function thereShouldBeNoAnymore(string $email): void
    {
        Assert::false($this->indexPage->isSingleResourceOnPage(['email' => $email]));
    }

    /**
     * @Then I should be notified that it cannot be deleted
     */
    public function iShouldBeNotifiedThatItCannotBeDeleted(): void
    {
        $this->notificationChecker->checkNotification(
            'Cannot remove currently logged in user.',
            NotificationType::failure()
        );
    }

    /**
     * @Then /^I should see the "([^"]*)" image as (my) avatar$/
     */
    public function iShouldSeeTheImageAsMyAvatar(string $avatar, AdminUserInterface|Proxy $administrator): void
    {
        if ($administrator instanceof AdminUserInterface) {
            $this->objectManager->refresh($administrator);
        }

        if ($administrator instanceof Proxy) {
            $$administrator->_refresh();
        }

        Assert::same($this->sharedStorage->get($avatar), $administrator->getAvatar()?->getPath());
    }

    /**
     * @Then /^I should see the "([^"]*)" avatar image in the top bar next to my name$/
     */
    public function iShouldSeeTheAvatarImageInTheTopBarNextToMyName(string $avatar): void
    {
        Assert::true($this->topBarElement->hasAvatarInMainBar($avatar));
    }

    private function getPath(AdminUserInterface|Proxy $administrator): string
    {
        if ($administrator instanceof AdminUserInterface) {
            $this->objectManager->refresh($administrator);
        }

        if ($administrator instanceof Proxy) {
            $administrator->_refresh();
        }

        $avatar = $administrator->getAvatar();
        if (null === $avatar) {
            return '';
        }

        return $avatar->getPath() ?? '';
    }

    private function updateAvatar(string $avatar, AdminUserInterface|Proxy $administrator): string
    {
        $this->updatePage->attachAvatar($avatar);
        $this->updatePage->saveChanges();

        return $this->getPath($administrator);
    }
}
