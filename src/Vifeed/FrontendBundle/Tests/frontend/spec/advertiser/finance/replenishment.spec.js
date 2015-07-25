describe("advertiser's replenishment page", function () {
  it('should be shown if a user is signed in', function () {
    browser.get('/');
    element(by.id('signin-link')).click();
    element(by.name('email')).sendKeys('test@mail.com');
    element(by.name('password')).sendKeys('test321');
    element(by.id('signin-button')).click();

    browser.sleep(1000);
    browser.get('/#/finance/replenishment');
    expect(element(by.tagName('h1')).getText()).toBe('Пополнение счета');
  });

  it('should have correct header', function () {
    browser.get('/#/finance/replenishment');
    expect(element(by.tagName('h1')).getText()).toBe('Пополнение счета');
  });

  it('should have choose buttons', function () {
    browser.get('/#/finance/replenishment');
    var buttons = element.all(by.css('.btn-group button'));
    expect(buttons.count()).toBe(3);
    expect(buttons.get(0).getText()).toBe('Робокасса');
    expect(buttons.get(1).getText()).toBe('Paypal');
    expect(buttons.get(2).getText()).toBe('Qiwi');
  });

  it('should display the phone field if the qiwi button has been chosen', function () {
    browser.get('/#/finance/replenishment');
    var qiwiButton = element.all(by.css('.btn-group button')).get(2);
    qiwiButton.click();
    expect(browser.isElementPresent(by.id('phone'))).toBeTruthy();
  });

  it('should not be shown if a user is signed out', function () {
    element(by.id('signout-link')).click();
    browser.sleep(1000);
    browser.get('/#/finance/replenishment');
    expect(element(by.tagName('h1')).getText()).toBe('Как это работает');
  });
});
