import React, { Component } from "react";
import RequestsList from './RequestsList.jsx';
import ExpectationsList from './ExpectationsList.jsx';
import AddExpectationForm from './AddExpectationForm.jsx';

export default class PageContent extends Component {
    render() {
        const tab = this.props.currentTab;
        let displayTab = <RequestsList />;
        if (tab === "listExpectations") {
            displayTab = <ExpectationsList />;
        } else if (tab === 'addExpectation') {
            displayTab = <AddExpectationForm />;
        }
        return (
            <section name="main-content">
                <div id="main-content" className="container">{displayTab}</div>
            </section>
        );
    }
}