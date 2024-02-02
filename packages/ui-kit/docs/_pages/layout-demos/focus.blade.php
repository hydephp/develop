<style>
    #contentArea {
        width: 100%;
        height: calc(100vh - 8rem - 66px - 62px - 4rem);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        background: #80808080;
        position: relative;
    }
    #contentArea::after {
        content: "";
        z-index: 1;
        bottom: -4rem;
        position: absolute;
        background-image: linear-gradient(to bottom,
            rgba(128, 128, 128, 0.5),
            rgba(128, 128, 128, 0));
        width: 100%;
        height: 4rem;
    }
    .contentArea p {
        margin-top: 0.25rem;
    }
</style>
<x-hyde-ui::layouts.focus>
    <div id="contentArea">
        <x-hyde-ui::heading>
            Content Area
        </x-hyde-ui::heading>
        <p>(Focus Layout)</p>
    </div>
</x-hyde-ui::layouts.focus>
