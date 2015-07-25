describe('advertiser index page', function () {
  it('should be shown if a user is signed in', function () {
    browser.get('/');
    element(by.id('signin-link')).click();
    element(by.name('email')).sendKeys('test@mail.com');
    element(by.name('password')).sendKeys('test321');
    element(by.id('signin-button')).click();
    browser.sleep(1000);
    expect(element(by.tagName('h1')).getText()).toBe('Управление кампаниями');
  });

  it('should have exact names if a user specified them', function () {
    browser.get('/#/profile');
    var firstName = element(by.id('first-name')),
        lastName = element(by.id('last-name')),
        phone = element(by.id('phone'));
    firstName.clear();
    firstName.sendKeys('Сидор');
    lastName.clear();
    lastName.sendKeys('Сидоров');
    phone.clear();
    phone.sendKeys('1231231212');
    element(by.id('save-profile-button')).click();
    browser.sleep(1000);
    var list = element.all(by.css('.navbar-right li'));
    expect(list.get(0).getText()).toBe('Сидор Сидоров');
  });

  it('should not be shown if a user is signed out', function () {
    element(by.id('signout-link')).click();
    browser.sleep(1000);
    expect(element(by.tagName('h1')).getText()).toBe('Как это работает');
  });
});
