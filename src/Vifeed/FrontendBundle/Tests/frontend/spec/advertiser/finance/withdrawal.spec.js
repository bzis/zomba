describe("advertiser's withdrawal page", function () {
  it('should be not shown if a user is signed in', function () {
    browser.get('/');
    element(by.id('signin-link')).click();
    element(by.name('email')).sendKeys('test@mail.com');
    element(by.name('password')).sendKeys('test321');
    element(by.id('signin-button')).click();

    browser.sleep(1000);
    browser.get('/#/finance/withdrawal');
    expect(element(by.tagName('h1')).getText()).toBe('Мои финансы общие расходы');
  });

  it('should not be shown if a user is signed out', function () {
    element(by.id('signout-link')).click();
    browser.sleep(1000);
    browser.get('/#/finance/withdrawal');
    expect(element(by.tagName('h1')).getText()).toBe('Как это работает');
  });
});
