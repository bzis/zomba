describe("publisher's withdrawal page", function () {
  it('should be not shown if a user is signed in and goes on the withdrawal page', function () {
    browser.get('/');
    element(by.id('signin-link')).click();
    element(by.name('email')).sendKeys('bangodrop2@gmail.com');
    element(by.name('password')).sendKeys('test321');
    element(by.id('signin-button')).click();

    browser.sleep(1000);
    browser.get('/#/finance/withdrawal');
    expect(element(by.tagName('h1')).getText()).toBe('Вывод средств');
  });

  it('should be shown with the selected wallet', function () {
    browser.get('/#/finance/withdrawal/3');
    expect(element(by.id('wallet-name')).getText()).toContain('WebMoney:');
  });

  it('should be shown with not selected wallet', function () {
    browser.get('/#/finance/withdrawal');
    expect(element(by.id('wallet-name')).getText()).toContain('Qiwi:');
  });

  it('should be shown with not existed wallet', function () {
    browser.get('/#/finance/withdrawal/2');
    expect(element(by.id('wallet-name')).getText()).toContain('Qiwi:');
  });

  it('should display an error if a user does not specify an amount', function () {
    browser.get('/#/finance/withdrawal');
    element(by.id('withdrawal-button')).click();
    expect(element(by.css('.help-block')).getText()).toBe('Вы забыли указать сумму');
  });

  it('should display an error if a user specifies too high amount', function () {
    browser.get('/#/finance/withdrawal');
    element(by.id('amount')).sendKeys('999999');
    element(by.id('withdrawal-button')).click();
    var errorList = element.all(by.repeater('errorMessage in validationErrors'));
    expect(errorList.count()).toEqual(1);
  });

  it('should display the success message if a user did well', function () {
    browser.get('/#/finance/withdrawal');
    element(by.id('amount')).sendKeys('1');
    element(by.id('withdrawal-button')).click();
    expect(element(by.tagName('h4')).getText()).toBe('Ваша заявка на вывод средств принята');
  });

  it('should not be shown if a user is signed out', function () {
    element(by.id('signout-link')).click();
    browser.sleep(1000);
    expect(element(by.tagName('h1')).getText()).toBe('Как это работает');
  });
});
