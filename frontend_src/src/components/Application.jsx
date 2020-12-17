import React, {Component} from 'react';
import Navigation from './Navigation.jsx';
import PageContent from './PageContent.jsx';

export default class Application extends Component {
    constructor (props) {
        super(props);
        this.state = {
            currentTab: "listRequests"
        };
    }

    changeTab(tab) {
        this.setState(Object.assign({}, this.state, {currentTab: tab}));
    }

    renderHeader() {
        return <Navigation onClick={(tab) => this.changeTab(tab)} />;
    }

    renderMainSection() {
        return <PageContent currentTab={this.state.currentTab} />;
    }

    renderFooter() {
        return <footer></footer>;
    }

    render() {
        return (
            <div id="page-content" className="container-fluid">
                {this.renderHeader()}
                {this.renderMainSection()}
                {this.renderFooter()}
            </div>
        );
      }
}
