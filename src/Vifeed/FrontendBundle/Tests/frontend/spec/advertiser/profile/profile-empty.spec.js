describe('advertiser index page', function () {
  it('should be shown if a user is signed in', function () {
    browser.get('/');
    element(by.id('signin-link')).click();
    element(by.name('email')).sendKeys('terranisu@gmail.com');
    element(by.name('password')).sendKeys('test321');
    element(by.id('signin-button')).click();
    browser.sleep(1000);
    expect(element(by.tagName('h1')).getText()).toBe('Управление кампаниями');
  });

  it('should have the profile link if a user does not specify a name', function () {
    browser.get('/');
    var list = element.all(by.css('.navbar-right li'));
    expect(list.get(0).getText()).toBe('Профиль');
  });

  it('should not be shown if a user is signed out', function () {
    element(by.id('signout-link')).click();
    browser.sleep(1000);
    expect(element(by.tagName('h1')).getText()).toBe('Как это работает');
  });
});
